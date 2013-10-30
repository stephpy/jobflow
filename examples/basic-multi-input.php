<?php

require_once __DIR__.'/../vendor/autoload.php';

use Rezzza\Jobflow\Jobs;
use Rezzza\Jobflow\Extension;
use Rezzza\Jobflow\Io;
use Rezzza\Jobflow\Extension\ETL\Type;

$builder = Jobs::createJobsBuilder();
$builder->addExtension(new Extension\Monolog\MonologExtension(new \Monolog\Logger('jobflow')));

$jobflowFactory = $builder->getJobflowFactory();
$jobFactory = Jobs::createJobFactory();

// We will inject IO to our job to indicate where Extractor needs to read and where Loader needs to write
$io = new Io\IoDescriptor(
    new Io\InputAggregator(array(
        new Io\Input('file://'.__DIR__.'/fixtures-om.csv'),
        new Io\Input('file://'.__DIR__.'/fixtures.csv'),
    )),
    new Io\Output('file:///'.__DIR__.'/temp/result.json')
);

// Here we go, you can build job on the fly
$job = $jobFactory
    ->createBuilder('job') // 'job' is the JobType by default.
    ->add(
        'example_extractor', // name
        new Type\Extractor\CsvExtractorType(), // or you can use 'csv_extractor' as ETLExtension is loaded by default
        array(
            'io' => $io
        )
    )
    ->add(
        'example_transformer', // name
        new Type\Transformer\CallbackTransformerType(), // or 'callback_transformer'
        array(
            'callback' => function($data, $target) {
                $target['firstname'] = $data[0];
                $target['name'] = $data[1];
                $target['url'] = sprintf('http://www.lequipe.fr/Football/FootballFicheJoueur%s.html', $data[2]);

                return json_encode($target, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        )
    )
    ->add(
        'example_loader', // name
        new Type\Loader\FileLoaderType(), // or 'file_loader'
        array(
            'io' => $io
        )
    )
    ->getJob() // builder create the Job with this method
;

// Create the scheduler responsible for the job execution
$jobflow = $jobflowFactory->create('php');

// Now we can execute our job
$jobflow
    ->setJob($job)
    ->init() // Will create the first message to run the process
    ->run()
;
