<?php

namespace app\models;

use Unirest\Request;

class JiraApi
{
    public $authorization = "Basic cGF2ZWwuZ3ViYW5vdkBpY254LnJ1Om1QZ0RwaENsSHRsNEN5V1pMYUdrRjhBMA==";
    public $headers;
    public $query;

    public function __construct(array $query = [])
    {
        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => $this->authorization
        ];
        $this->query = $query;
    }

    public function getAllProject()
    {
        return Request::get(
            'https://icnx.atlassian.net/rest/api/3/project',
            $this->headers
        );
    }

    public function getTaskProject($key_project)
    {
        return Request::get(
            'https://icnx.atlassian.net/rest/api/3/search?jql=project=' . $key_project,
            $this->headers,
            'jql=project=' . $key_project . ' AND timeSpent > 0'
        );
    }

    public function getWorklog($issueKey, $startedAfter = null)
    {
//        ?startedAfter=' . $startedAfter
        return Request::get(
            'https://icnx.atlassian.net/rest/api/3/issue/' . $issueKey . '/worklog',
            $this->headers
        );
    }
}