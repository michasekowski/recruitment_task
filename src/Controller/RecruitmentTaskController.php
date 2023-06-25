<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Utils\Constants;
use App\Utils\Util;

class RecruitmentTaskController
{
    public function process(): Response
    {
		$reviews = array();
		$crash_reports = array();
		$duplicates = array();
		
		if (str_contains(shell_exec('dir'), 'recruitment-task-source.json')) {
			$output = shell_exec('TYPE recruitment-task-source.json');
			$decoded_json = json_decode($output, true);
			
			Util::console_log('Processing messages');
			foreach($decoded_json as $object) {
				if (str_contains(strtolower($object['description']), 'przegląd')) {
					if (str_contains($this->isDuplicate($reviews, $object['description']), 'false')) {
						$rev = $this->getReview($object);
						$rev = substr($rev, strpos($rev, "{"));
						array_push($reviews, json_decode($rev, true));
					}
					else {
						array_push($duplicates, $object);
						Util::console_log('Message number '.$object['number'].' is duplicate');
					}
				}				
				else {
					if (str_contains($this->isDuplicate($crash_reports, $object['description']), 'false')) {
						$crash_rep = $this->getCrashReport($object);
						$crash_rep = substr($crash_rep, strpos($crash_rep, "{"));
						array_push($crash_reports, json_decode($crash_rep, true));
					}
					else {
						array_push($duplicates, $object);
						Util::console_log('Message number '.$object['number'].' is duplicate');
					}
				}							
			}
			Util::console_log('Processing finished');
				
			file_put_contents('reviews.json', json_encode($reviews, JSON_UNESCAPED_UNICODE));
			file_put_contents('crash_reports.json', json_encode($crash_reports, JSON_UNESCAPED_UNICODE));
			file_put_contents('unprocessed_messages.json', json_encode($duplicates, JSON_UNESCAPED_UNICODE));
			
			$reviews_num = count($reviews);
			$crash_reports_num = count($crash_reports);
			$processed_messages_num = $reviews_num + $crash_reports_num;
			Util::console_log('Number of processed messages: '.$processed_messages_num);
			Util::console_log('Number of reviews created: '.$reviews_num);
			Util::console_log('Number of crash reports created: '.$crash_reports_num);
			
			return new Response(
				'<html><body>Data processed</body></html>'
			);
		}
		else {
			return new Response(
				'<html><body>File recruitment-task-source.json not found</body></html>'
			);
		}
		
    }
	
	private function getReview($object): Response {
		$description = $object['description'];
		$type = Constants::TYPE_REVIEW;
		
		if (empty($object['dueDate'])) {
			$review_date = '';
			$week = '';
			$status = Constants::STATUS_NEW;
		}
		else {
			$review_date = $object['dueDate'];
			$week = idate('W', strtotime($object['dueDate']));
			$status = Constants::STATUS_PLANNED;
		}
		
		$recommendations = '';
		$phone = $object['phone'];
		$creation_date = '';
		
		$response = array("description"=>$description,"type"=>$type,"review_date"=>$review_date,"week"=>$week,"status"=>$status,"recommendations"=>$recommendations,"phone"=>$phone,"creation_date"=>$creation_date);		
		return new Response(json_encode($response, JSON_UNESCAPED_UNICODE));
	}
	
	private function getCrashReport($object): Response {
		$description = $object['description'];
		$type = Constants::TYPE_CRASH_REPORT;
		
		if (str_contains(strtolower($object['description']), 'bardzo pilne'))
			$priority = Constants::PRIORITY_CRITICAL;
		elseif (str_contains(strtolower($object['description']), 'pilne'))
			$priority = Constants::PRIORITY_HIGH;
		else	
			$priority = Constants::PRIORITY_NORMAL;
		
		if (empty($object['dueDate'])) {
			$service_date = '';
			$status = Constants::STATUS_NEW;
		}
		else {
			$service_date = $object['dueDate'];
			$status = Constants::STATUS_TERM;
		}
		
		$service_comments = '';
		$phone = $object['phone'];
		$creation_date = '';
		
		$response = array("description"=>$description,"type"=>$type,"priority"=>$priority,"service_date"=>$service_date,"status"=>$status,"service_comments"=>$service_comments,"phone"=>$phone,"creation_date"=>$creation_date);
		return new Response(json_encode($response, JSON_UNESCAPED_UNICODE));
	}
	
	private function isDuplicate($objects_array, $description): Response {		
		foreach($objects_array as $object) {
			if ($object['description'] == $description)
				return new Response('true');
		}
		return new Response('false');
	}	
}