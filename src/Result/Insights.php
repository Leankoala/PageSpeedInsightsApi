<?php

namespace Leankoala\PageSpeedInsights\Result;

class Insights
{
    private $insightsArray;

    public function __construct($insightsArray)
    {
        $this->insightsArray = $insightsArray;
    }

    public function getScore()
    {
        return $this->insightsArray["ruleGroups"]["SPEED"]["score"];
    }

    public function getPlainInsights()
    {
        return $this->insightsArray;
    }
}