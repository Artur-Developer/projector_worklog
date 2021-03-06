<?php

namespace app\commands;

use app\models\BuildReport;
use app\models\JiraAlert;
use app\models\JiraApi;
use app\models\JiraProjects;
use app\models\JiraTasks;
use app\models\JiraWorklogs;
use app\models\LibTasks;
use app\models\ProTimesheet;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use Yii;

// check user_id between pro_timesheet and jira_work_logs

//SELECT pt.*,b.id_timesheet, u.id_user AS save_u_id
//FROM projector.sys_users u
//JOIN (
//
//    SELECT jw.id_timesheet, jw.emailAdress
//FROM projector.jira_worklogs jw
//)b ON b.emailAdress LIKE u.email
//INNER JOIN pro_timesheet pt ON pt.id_timesheet = b.id_timesheet

class JiraController extends Controller
{
    public function getMessage($text) {
        print  $text . "\n";
    }

    public function setLibTask($response_task, $project_id, $id_work = 14) {
        set_time_limit(0);
        if (empty($project_id) || intval($project_id) == 0) {
            return '';
        }
        if ($response_task->fields->issuetype->subtask == false){
            $summary = $response_task->fields->summary;
        } else if ($response_task->fields->issuetype->subtask == true){
            $summary = $response_task->fields->parent->fields->summary . ' - ' . $response_task->fields->summary;
        } else {
            $summary = 'empty';
            $this->getMessage('lib task empty ' . 'jira_task => ' . $response_task->id);

            JiraAlert::sendMail(
                'Lib value name == empt, jira_task.id == ' . $response_task->id . '<br> 
                [$response_task->fields->parent->fields->summary] => ' . $response_task->fields->parent->fields->summary . '<br>
                [$response_task->fields->issuetype->subtask] =>' . $response_task->fields->issuetype->subtask . '<br>'
            );
        }

        $new_lib_task = new LibTasks();
        $new_lib_task->name = $summary;
        $new_lib_task->id_work = $id_work;
        $new_lib_task->id_prj = $project_id;
        $new_lib_task->validate() && $new_lib_task->save()
            ? $this->getMessage("create lib_task for table lib_tasks " .
            " | id => " . $new_lib_task->id_task . " project_id => " . $new_lib_task->id_prj)
            : $this->getMessage("error create lib_task for table lib_tasks  " .
            " | id => " . $response_task->id . " id_task => " . $response_task->fields->project->key);

        return $new_lib_task->id_task;
    }

    public function getWorkLogs($task_id, $id_pro_project, $id_task, $build_report)
    {
        set_time_limit(0);
        $request = new JiraApi();
        $response = $request->getWorklog($task_id);
        if (isset($response->code) && $response->code != 200) {
            $error = "code = " . $response->code . "; " . isset($response->raw_body)
                ? strval($response->raw_body)
                : json_encode($response->errorMessages);

//            JiraAlert::sendMail("Iteration _3_ get issue work_logs \n" . $error);
        }
        if (!empty($response->body->worklogs) && count($response->body->worklogs) > 0) {
            foreach ($response->body->worklogs as $key_work_logs => $val_work_logs) {
                if (!empty($val_work_logs->author->emailAddress)) {
                    $sys_user_id = $this->getUserId($val_work_logs->author->emailAddress); // get user_id
                    $sys_user_id = empty($sys_user_id['id_user']) ? 0 : $sys_user_id['id_user'];
                    $work_logs = JiraWorkLogs::findOne($val_work_logs->id);
                    if (!empty($work_logs->id)) {
                        //update work_logs
                        $work_logs->updateWorkLog(
                            $val_work_logs->id,
                            $task_id,
                            $val_work_logs->author->emailAddress,
                            $val_work_logs->timeSpentSeconds,
                            $val_work_logs->started,
                            $val_work_logs->created,
                            $this->pub_today()
                        )
                            ? $this->getMessage("update work_logs for table jira_work_logs " .
                            " | id => " . $val_work_logs->id . " task_id => " . $val_work_logs->issueId) ?: $build_report->setEntity('update_worklog')
                            : $this->getMessage("error update work_logs for table jira_work_logs  " .
                            " | id => " . $work_logs->id . " id_task => " . $work_logs->issueId);

                        // To go point (4) for carry data about used time in table 'projector.pro_timesheet'.
                        $time_sheet_one = ProTimesheet::findOne($work_logs->id_timesheet);
                        if (!empty($time_sheet_one->id_timesheet)) { // if not empty proTimeSheet
                            $this->getMessage('updateProTimeSheet id_pro_project = ' . $id_pro_project);
                            $time_sheet_one->saveProTimeSheet( // update proTimeSheet
                                $id_pro_project,
                                $id_task, // lib_task.id after created
                                $sys_user_id,
                                $work_logs->timeSpentSeconds,
                                $this->pub_today($work_logs->created)
                            )
                                ? $this->getMessage("save pro_timesheet for table pro_timesheet " .
                                " | id => " . $work_logs->id . " task_id => " . $work_logs->issueId) ?: $build_report->setEntity('update_timesheet')
                                : $this->getMessage("error save pro_timesheet for table pro_timesheet  " .
                                " | id => " . $val_work_logs->id . " id_task => " . $val_work_logs->issueId);
                        } else { // if empty proTimeSheet
                            $this->getMessage('newProTimeSheet id_pro_project = ' . $id_pro_project);
                            $this->newProTimeSheet(
                                $work_logs, // object finded work log
                                $val_work_logs, // api response item work_log
                                $id_pro_project,
                                $id_task, // lib_task.id after created
                                $sys_user_id,
                                $work_logs->timeSpentSeconds,
                                $this->pub_today($work_logs->created),
                                $build_report
                            );
                        }
                    } else {
                        $work_logs = new JiraWorklogs();
                        $work_logs->id = $val_work_logs->id;
                        $work_logs->issueId = $task_id;
                        $work_logs->emailAdress = $val_work_logs->author->emailAddress;
                        $work_logs->timeSpentSeconds = $val_work_logs->timeSpentSeconds;
                        $work_logs->started = $val_work_logs->started;
                        $work_logs->created = $val_work_logs->created;
                        $work_logs->updated = $this->pub_today();
                        $work_logs->save()
                            ? $this->getMessage("save work_logs for table jira_work_logs " .
                            " | id => " . $work_logs->id . " task_id => " . $work_logs->issueId) ?: $build_report->setEntity('new_worklog')
                            : $this->getMessage("error save work_logs for table jira_work_logs  " .
                            " | id => " . $val_work_logs->id . " id_task => " . $val_work_logs->issueId);

                        // To go point (4) for carry data about used time in table 'projector.pro_timesheet'.
                        $this->getMessage('newProTimeSheet id_pro_project = ' . $id_pro_project);
                        $this->newProTimeSheet(
                            $work_logs, // object finded work log
                            $val_work_logs, // api response item work_log
                            $id_pro_project,
                            $id_task, // lib_task.id after created
                            $sys_user_id,
                            $work_logs->timeSpentSeconds,
                            $this->pub_today($val_work_logs->created),
                            $build_report
                        );
                    }
                }
            }
        }
    }

    // this methods need for converte and save pro_timesheet in field report_time
    // it's not my code! it's legacy projector
    public function pub_day_return($init)
    {
        $time_format = $this->pub_format_date(array("unixtime" => $init));
        return mktime(0,0,0,$time_format["n"], $time_format["d"], $time_format["Y"]);
    }

    public function pub_today($date = null)
    {
        date_default_timezone_set('UTC');
        if ($date == null){
            $time = strtotime(date('Y-m-d'));
        } else {
            $t = new \DateTime($date);
            $t = $t->format('Y-m-d');
            $time = strtotime($t);
        }
        return $time;
//        return $this->pub_day_return($time);
    }

    public function pub_format_date($init)
    {
        $current_date["d"] = date("d", $init["unixtime"]);
        $current_date["m"] = date("m", $init["unixtime"]);
        $current_date["n"] = date("n", $init["unixtime"]);
        $current_date["Y"] = date("Y", $init["unixtime"]);

        $current_date["W"] = date("w", $init["unixtime"]);
        $current_date["M"] = date("n", $init["unixtime"]) - 1;

        return $current_date;
    }
    ///////////////////////////////////////////////////////////////////////////////

    public function newProTimeSheet ($work_logs, $response_val_work_logs, $id_project, $id_task, $id_user, $spend_time, $report_time, $build_report) {
        $this->getMessage("////// Iteration _4_ update ProTimeSheet");

        $time_sheet = new ProTimesheet();
        $time_sheet->saveProTimeSheet(
            $id_project,
            $id_task,
            $id_user,
            $spend_time,
            $report_time
        )
        ? $this->getMessage("save pro_timesheet for table pro_timesheet " .
        " | id => " . $id_project . " task_id => " . $id_task) ?: $build_report->setEntity('new_timesheet')
        : $this->getMessage("error save pro_timesheet for table pro_timesheet  " .
        " | id => " . $response_val_work_logs->id . " id_task => " . $response_val_work_logs->issueId);
        // updated field id_timesheet from table jira_worklogs
        $work_logs->id_timesheet = $time_sheet->id_timesheet;
        $work_logs->save()
            ? $this->getMessage("update id_timesheet for table work_log " .
            " | id => " . $id_project . " task_id => " . $id_task)
            : $this->getMessage("error update id_timesheet for table work_log  " .
            " | id => " . $response_val_work_logs->id . " id_task => " . $response_val_work_logs->issueId);
    }

    public function getUserId($email)
    {
        return Yii::$app->db->createCommand('
                SELECT su.id_user FROM projector.sys_users su WHERE su.email like "%' . $email . '%"')
            ->queryOne();
    }

    public function actionTestEmail(){

        $api = new JiraApi();
        echo '<pre>';
        print_r($api->getTaskProject('A03BMW20'));
        echo '</pre>';
//        JiraAlert::sendMail('check send email');
    }

    public function actionRunProjectorJira()
    {
        set_time_limit(0);
        $build_report = new BuildReport();
        $request = new JiraApi();
        $response = $request->getAllProject();
        if (isset($response->code) && $response->code != 200) {
            $error = "code = " . $response->code . "; " . isset($response->raw_body)
                ? strval($response->raw_body)
                : json_encode($response->errorMessages);

//            JiraAlert::sendMail("Iteration _1_ all projects \n" .$error);
        }
        if (count($response->body) > 0) {
            $this->getMessage("////// Iteration _1_ projects");

            foreach ($response->body as $key_response => $response_project) {
                $object_project = JiraProjects::findOne($response_project->id);
                if (!empty($object_project->id)) {
                    $object_project->updateProject( // update project
                        $response_project->id,
                        $response_project->name,
                        $response_project->key
                    ) === true
                        ? $this->getMessage("update project " . $object_project->key) ?: $build_report->setEntity('update_prj')
                        : $this->getMessage("error update project" . $response_project->key);
                } else {
                    $new_project = new JiraProjects();
                    $new_project->newProject( // new project
                        $response_project->id,
                        $response_project->name,
                        $response_project->key
                    ) === true
                        ? $this->getMessage("save new project " . $new_project->key) ?: $build_report->setEntity('new_prj')
                        : $this->getMessage("error save " . $response_project->key);
                }
            }
            $search_id_project = Yii::$app->db->createCommand('SELECT * FROM (
                        select pp.id_project, concat(pp.nom , lc.shortname, pp.year_small) as key_project
                    from projector.pro_project pp
                    inner join projector.lib_clients lc  on lc.id_client = pp.client_dictionary_value) a
                    JOIN (select jp.id,jp.name from projector.jira_projects jp) b ON b.name LIKE CONCAT(a.key_project, "%")')
                ->queryAll();

            $this->getMessage("///// > count(" . count($search_id_project) . ") project exist in db table pro_project");
            if (count($search_id_project) > 0) {
                $search_id_project = ArrayHelper::index($search_id_project, null, 'id');
                foreach ($search_id_project as $key_search_id_project => $value_search_id_project) {
                    foreach ($value_search_id_project as $key_project_like => $project_like)  {
                        if ($key_project_like == 0) {
                            $add_project_id = Yii::$app->db->createCommand("
                                UPDATE projector.jira_projects jp SET jp.id_project = " . $project_like['id_project'] . "
                                WHERE id = " . $project_like['id'])->execute();
                            $add_project_id == 1 // if success update id_project
                                ? $this->getMessage("update id_project for table jira_projects " .
                                " | id => " . $project_like['id'] . " id_project => " . $project_like['id_project'])
                                : $this->getMessage("error update for table jira_projects  " .
                                " | id => " . $project_like['id'] . " id_project => " . $project_like['id_project']);
                        }
                    }
                }
            }
        }
        // Iteration _2_ all task in project
        $all_project = JiraProjects::find()->where(["is not", "id_project", null])->all();
        $request = new JiraApi();
        $this->getMessage("////// Iteration _2_ tasks in project");
        foreach ($all_project as $key_project => $project) {
            $response = $request->getTaskProject($project->key);
            $response_task = $response->body;
            if (isset($response->code) && $response->code != 200) {
                $build_report->setEntity('not_search_task_in_prj');
            }
            if (!empty($response_task->issues) && count($response_task->issues) > 0) {
                foreach ($response_task->issues as $key_response_task => $value_response_task) {
                    $t_timespent = $value_response_task->fields->timespent;
                    if (empty($t_timespent) || $t_timespent == null || $t_timespent == 0) {
                        continue;
                    } else {
                        $check_task = JiraTasks::findOne($value_response_task->id);
                        if (!empty($check_task->id)) {
                            // task don't updated
                            if ($check_task->timespent != $value_response_task->fields->timespent) {
                                $this->getWorkLogs($check_task->id, $project->id_project, $check_task->id_task, $build_report);
                            }
                            $check_task->updateTask(
                                $value_response_task->id,
                                $value_response_task->key,
                                $value_response_task->fields->project->id,
                                $value_response_task->fields->project->key,
                                $value_response_task->fields->summary,
                                $value_response_task->fields->timespent,
                                $value_response_task->fields->created,
                                $value_response_task->fields->updated
                            )
                                ? $this->getMessage("update task for table jira_projects " .
                                " | id => " . $check_task->id . " project_key => " . $check_task->project_key) ?: $build_report->setEntity('update_task')
                                : $this->getMessage("error update for table jira_projects  " .
                                " | id => " . $value_response_task->id . " id_project => " . $value_response_task->fields->project->key);

                        } else {
                            // new task
                            $new_task = new JiraTasks();
                            $new_task->id = $value_response_task->id;
                            $new_task->key = $value_response_task->key;
                            $new_task->project_id = $value_response_task->fields->project->id;
                            $new_task->project_key = $value_response_task->fields->project->key;
                            $new_task->summary = $value_response_task->fields->summary;
                            $new_task->timespent = $value_response_task->fields->timespent;
                            $new_task->created = $value_response_task->fields->created;
                            $new_task->updated = $value_response_task->fields->updated;
                            $new_task->save()
                                ? $this->getMessage("create task for table jira_tasks " .
                                " | id => " . $new_task->id . " project_key => " . $new_task->project_key) ?: $build_report->setEntity('new_task')
                                : $this->getMessage("error update for table jira_projects  " .
                                " | id => " . $value_response_task->id . " id_task => " . $value_response_task->fields->project->key);

                            // Than in table ‘projector.lib_tasks’ create post in that context

                            $lib_task_id = $this->setLibTask($value_response_task, $project->id_project);

                            $new_task->id_task = $lib_task_id;
                            $new_task->save()
                                ? $this->getMessage("update id_task for table jira_tasks " .
                                " | id => " . $new_task->id_task . " id_task => " . $new_task->id_task) ?: $build_report->setEntity('new_lib_task')
                                : $this->getMessage("error update id_task for table jira_tasks  " .
                                " | id => " . $value_response_task->id . " id_task => " . $value_response_task->fields->project->key);
                            //// get timesheets
                            $this->getMessage("////// Iteration _3_ get issue work_logs new " . $new_task->id);
                            $this->getWorkLogs($new_task->id, $project->id_project, $lib_task_id, $build_report);
                        }
                    }
                }
            }
        }
        $finish_time = new \DateTime();
        JiraAlert::sendMail($build_report->build($finish_time, "<br>"), 'REPORT JIRA-PROJECTOR INTEGRATION');
        $this->getMessage($build_report->build($finish_time));
    }
}
