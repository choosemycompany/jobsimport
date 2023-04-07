<?php

/************************************
Entry point of the project.
To be run from the command line.
************************************/

include_once(__DIR__.'/utils.php');
include_once(__DIR__.'/config.php');


printMessage("Starting...");

$files = [];
$count = 0;

if($folder = opendir(RESSOURCES_DIR))
    {
        while(($file = readdir($folder)))
        {
					$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					if (in_array($ext, ['xml', 'json'])) {
						$files[] = [
							'extension' => $ext,
							'file' => RESSOURCES_DIR . $file
						];
					}
        }
     }

		$jobsImporter = new JobsImporter(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB, $files);

		try {
			$count = $jobsImporter->importJobs();
		} catch (Exception $e) {
			printMessage("An error occurred");
		}
		printMessage("> {count} jobs imported.", ['{count}' => $count]);

/* list jobs */
		$jobsLister = new JobsLister(SQL_HOST, SQL_USER, SQL_PWD, SQL_DB);
		$jobs = $jobsLister->listJobs();

		printMessage("> all jobs ({count}):", ['{count}' => count($jobs)]);
		foreach ($jobs as $job) {
    printMessage(" {id}: {reference} - {title} - {publication}", [
    	'{id}' => $job['id'],
    	'{reference}' => $job['reference'],
    	'{title}' => $job['title'],
    	'{publication}' => $job['publication']
    ]);
}


printMessage("Terminating...");
