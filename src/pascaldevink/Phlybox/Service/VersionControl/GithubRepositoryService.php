<?php

namespace pascaldevink\Phlybox\Service\VersionControl;

class GithubRepositoryService implements VCSRepositoryService
{

    /**
     * @param $repositoryOwner
     * @param $repository
     * @param $boxName
     *
     * @return VCSRepositoryService
     */
    public function checkoutRepository($repositoryOwner, $repository, $boxName)
    {
        $command = "git clone git@github.com:$repositoryOwner/$repository.git ./$boxName";
        system($command);

        return $this;
    }

    /**
     * @param $boxName
     * @param $baseBranch
     *
     * @return VCSRepositoryService
     */
    public function setRepositoryBranch($boxName, $baseBranch)
    {
        $command = "cd $boxName && git checkout $baseBranch";
        system($command);

        return $this;
    }

    /**
     * @param $repositoryOwner
     * @param $repository
     * @param $prNumber
     *
     * @return array
     */
    public function getInfoForPullRequest($repositoryOwner, $repository, $prNumber)
    {
        $curlUrl = "https://api.github.com/repos/$repositoryOwner/$repository/pulls/$prNumber";
        $ch = curl_init($curlUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, "phlybox");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rawOutput = curl_exec($ch);
        curl_close($ch);

        $output = json_decode($rawOutput);

        return $output;
    }

    /**
     * @param $boxName
     * @param $baseBranch
     * @param $prUrl
     * @param $prBranch
     *
     * @return VCSRepositoryService
     */
    public function pullInPullRequest($boxName, $baseBranch, $prUrl, $prBranch)
    {
        $command = "cd $boxName && git checkout -b $prBranch $baseBranch";
        system($command);

        $command = "cd $boxName && git pull $prUrl $prBranch";
        system($command);

        $command = "cd $boxName && git checkout $baseBranch";
        system($command);

        $command = "cd $boxName && git merge $prBranch";
        system($command);

        return $this;
    }
}