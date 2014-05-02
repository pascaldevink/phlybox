<?php

namespace pascaldevink\Phlybox\Controller;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use pascaldevink\Phlybox\Service\GithubRepositoryService;
use pascaldevink\Phlybox\Service\SlackNotificationService;
use pascaldevink\Phlybox\Service\VagrantService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    public function upAction(Request $request)
    {
        $text = $request->get('text');
        list($trigger, $repositoryOwner, $repository, $baseBranch, $prNumber) = explode(' ', $text);

        $logger = new Logger('webhook');
        $logger->pushHandler(new StreamHandler(dirname(__FILE__) . '/incoming.log'), LOGGER::DEBUG);

        $logger->debug(sprintf('Getting %s:%s:%s <= %s', $repositoryOwner, $repository, $baseBranch, $prNumber));

        $vcsRepositoryService = new GithubRepositoryService();
        $vagrantService = new VagrantService();
        $notificationService = new SlackNotificationService('autotrack', 'fMxE9J6BYNerp3pnKd8bE9TY', '#deploys', 'phlybox');

        $boxName = $vagrantService->generateBoxName();
        $boxIp = $vagrantService->generateBoxIp();

        $vcsRepositoryService->checkoutRepository($repositoryOwner, $repository, $boxName);
        $vcsRepositoryService->setRepositoryBranch($boxName, $baseBranch);

        $prInfoOutput = $vcsRepositoryService->getInfoForPullRequest($repositoryOwner, $repository, $prNumber);
        $prUrl = $this->getPRUrlFromPRInfo($prInfoOutput);
        $prBranch = $this->getPRBranchFromPRInfo($prInfoOutput);

        $vcsRepositoryService->pullInPullRequest($boxName, $baseBranch, $prUrl, $prBranch);

        $vagrantService->vagrantUp($boxName, $boxIp);

        $notificationService->notify("Box is up for $prUrl at: http://$boxIp");

        return new Response(
                    sprintf("Box is up for %s at: http://%s", $prUrl, $boxIp)
                );
    }

    protected function getPRUrlFromPRInfo($prInfoOutput)
    {
        return $prInfoOutput->head->repo->ssh_url;
    }

    protected function getPRBranchFromPRInfo($prInfoOutput)
    {
        return $prInfoOutput->head->ref;
    }
} 