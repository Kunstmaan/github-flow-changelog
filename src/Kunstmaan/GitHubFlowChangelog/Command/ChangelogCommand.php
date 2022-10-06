<?php

namespace Kunstmaan\GitHubFlowChangelog\Command;

use Github\AuthMethod;
use Github\Client;
use Github\ResultPager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ChangelogCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('changelog')
            ->setDescription('Generates the changelog')
            ->addArgument("token", InputArgument::REQUIRED, "The GitHub API token")
            ->addArgument("organisation", InputArgument::REQUIRED, "The GitHub organisation")
            ->addArgument("repository", InputArgument::REQUIRED, "The GitHub repository")
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $changelog = [];

        $client = new Client();
        $client->authenticate($input->getArgument('token'), null, AuthMethod::ACCESS_TOKEN);

        $pullRequestAPI = $client->api('pull_request');

        $paginator = new ResultPager($client);
        $parameters = [$input->getArgument('organisation'), $input->getArgument('repository'), ['state' => 'closed']];
        $pullRequests = $paginator->fetchAll($pullRequestAPI, 'all', $parameters);

        $mergedPullRequests = array_filter($pullRequests, static function ($pullRequest) {
            return !empty($pullRequest['merged_at']);
        });

        foreach ($mergedPullRequests as $pullRequest) {
            if (empty($pullRequest['milestone'])){
                $milestone = 'No Milestone Selected';
            } else {
                $dueDate = $pullRequest['milestone']['due_on'];
                $date = $dueDate ? \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $dueDate) : new \DateTimeImmutable();
                $milestone = $pullRequest['milestone']['title'] . " / " . $date->format('Y-m-d');
            }

            if (!array_key_exists($milestone, $changelog)){
                $changelog[$milestone] = [];
            }

            $changelog[$milestone][] = $pullRequest;
        }

        uksort($changelog, 'version_compare');
        $changelog = array_reverse($changelog);

        echo "# Changelog";

        foreach($changelog as $milestone => $pullRequests){
            echo "\n\n## $milestone\n\n";

            foreach($pullRequests as $pullRequest) {
                echo "* ". $pullRequest['title'] . " [#".$pullRequest['number']."](".$pullRequest['html_url'].") ([@".$pullRequest['user']['login']."](".$pullRequest['user']['html_url'].")) \n";
            }
        }

        //var_dump($changelog);
        return 0;
    }
}
