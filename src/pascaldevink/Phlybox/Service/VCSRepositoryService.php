<?php

namespace pascaldevink\Phlybox\Service;

interface VCSRepositoryService
{
    /**
     * @param $repositoryOwner
     * @param $repository
     * @param $boxName
     *
     * @return VCSRepositoryService
     */
    public function checkoutRepository($repositoryOwner, $repository, $boxName);

    /**
     * @param $boxName
     * @param $baseBranch
     *
     * @return VCSRepositoryService
     */
    public function setRepositoryBranch($boxName, $baseBranch);

    /**
     * @param $repositoryOwner
     * @param $repository
     * @param $prNumber
     *
     * @return array
     */
    public function getInfoForPullRequest($repositoryOwner, $repository, $prNumber);

    /**
     * @param $boxName
     * @param $baseBranch
     * @param $prUrl
     * @param $prBranch
     *
     * @return VCSRepositoryService
     */
    public function pullInPullRequest($boxName, $baseBranch, $prUrl, $prBranch);

} 