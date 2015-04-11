<?php
namespace Kunstmaan\GitHubFlowChangelog\Command;

use Cilex\Command\Command;
use Github\Client;
use Github\ResultPager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangelogCommand extends Command {


    protected function configure()
    {
        $this
            ->setName('changelog')
            ->setDescription('Generates the changelog')
            ->addArgument("token", InputArgument::REQUIRED, "The GitHub API token")
            ->addArgument("organisation", InputArgument::REQUIRED, "The GitHub organisation")
            ->addArgument("repository", InputArgument::REQUIRED, "The GitHub repository")
        ;
    }


    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $changelog = array();

        $client = new Client(
            new \Github\HttpClient\CachedHttpClient(array('cache_dir' => '/tmp/github-api-cache'))
        );
        $client->authenticate($input->getArgument("token"), null, Client::AUTH_HTTP_TOKEN);


        $pullRequestAPI = $client->api('pull_request');

        $paginator = new ResultPager($client);
        $parameters = array($input->getArgument("organisation"), $input->getArgument("repository"), array('state' => 'closed'));
        $pullRequests = $paginator->fetchAll($pullRequestAPI, 'all', $parameters);

        $mergedPullRequests = array_filter($pullRequests, function($pullRequest) {
            return !empty($pullRequest["merged_at"]);
        } );

        foreach( $mergedPullRequests as $pullRequest ){
            if (empty($pullRequest['milestone'])){
                $milestone = "No Milestone Selected";
            } else {
                $milestone = $pullRequest['milestone']['title'] . " / " . strftime("%Y-%m-%d",strtotime($pullRequest['milestone']['due_on']));
            }

            if(!array_key_exists($milestone, $changelog)){
                $changelog[$milestone] = array();
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
    }
}
