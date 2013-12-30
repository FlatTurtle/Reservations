<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
/**
 * Author: Nik Torfs
 */
class AddCompany extends Command
{
    /**
     * The console command name
     *
     * @var string
     */
    protected $name = 'reservations:addCompany';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = "Add a company to your cluster";

    /**
     * Execute the console command
     *
     * @return void
     */
    public function fire(){

        $allowed_types = array('array', 'boolean', 'integer', 'number', 'null', 'object', 'string');

        $cluster = Cluster::where('clustername', '=', $this->option('cluster'))->first();
        if (!isset($cluster)) {
            $this->comment("This cluster doesn't exist.");
            return;
        }

        do{
            // get company name
            do {
                $name = $this->ask("Company name : ");
                if(empty($name))
                    $this->comment("Your company name is empty.");
            } while(empty($name));

            // check if a company with this name and cluster already exists
            $company = Company::byName($cluster, $name)->first();
            if(isset($company))
                $this->comment("A company with this name already exists, please choose another one.");
        }while(isset($company));

        // get company logo url
        do{
            $logo_url = $this->ask("Logo url: ");
            if(empty($logo_url))
                $this->comment("Your logo url is empty.");
        } while(empty($logo_url));


        $domains = [];
        // get company domains
        $add_domains = $this->ask("Do you want to add any domains (like: '@flatturtle.com')? [Y]/n") ? 0 : 1;
        while($add_domains){
            $domain = $this->ask("Domain: ");
            if(empty($domain)){
                $this->comment("Your domain is empty.");
            }

            array_push($domains, $domain);
            $add_domains = $this->ask("Do you want to add more domains (like: '@flatturtle.com')? [Y]/n") ? 0 : 1;
        };

        $company = Company::create(array(
            "name" => $name,
            "logo_url" => $logo_url,
            "cluster_id" => $cluster->id,
            "domains" => json_encode($domains)
        ));

        $this->info("Company '{$company->name}' has been saved.");
        return;
    }

    /**
     * Get the console command arguments
     *
     * @return array
     */
    protected function getArguments(){
        return array(
        );
    }

    /**
     * Get the console command options
     *
     * @return array
     */
    protected function getOptions(){
        return array(
            array('cluster', null, InputOption::VALUE_REQUIRED, 'Add company for this cluster')
        );
    }
}
