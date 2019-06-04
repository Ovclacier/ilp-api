<?php

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

function ilp_api_init_hooks(){
	add_action( 'rest_api_init', function () {
		register_rest_route( 'ilp-api/v1', '/course/all', array(
			'methods' => 'GET',
			'callback' => 'ilp_api_get_all_courses',
		));
		register_rest_route( 'ilp-api/v1', '/course/progress/', array(
			'methods' => 'GET',
			'callback' => 'ilp_api_get_progress_by_mail',
		));
		register_rest_route( 'ilp-api/v1', '/course/content/', array(
			'methods' => 'GET',
			'callback' => 'ilp_api_get_course_content',
		));
	});
}

if ( ! function_exists( 'ilp_api_get_all_courses' ) ) {
	function ilp_api_get_all_courses($data){
		$arr_query = array(
			'post_type'           => 'lp_course',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => - 1
		);
		return ilp_get_courses($arr_query);
	}
}

// sfsf: Marche pas
//if ( ! function_exists( 'ilp_api_get_course_by_id' ) ) {
//	function ilp_api_get_course_by_id($course_id){
//		$arr_query = array(
//			'post_type'           => 'lp_course',
//			'p'                   => intval($course_id),
//			'post_status'         => 'publish',
//			'ignore_sticky_posts' => true,
//			'posts_per_page'      => - 1
//		);
//		return ilp_get_courses($arr_query);
//	}
//}

if ( ! function_exists( 'ilp_get_courses' ) ) {
	function ilp_get_courses($arr_query){
		$courses_query = new WP_Query( $arr_query );
		if (! $courses_query->have_posts()) {
			$result = array(
				'courses' => null,
				'count' => 0
			);
		}else{
			$courses=array();
			$courses_count=$courses_query->post_count;
			$i=0;
			while ($courses_query->have_posts() ){
				$courses_query->the_post();
				$courses[$i]=array(
					'id' => learn_press_get_post(),
					'name' => get_the_title(),
					'permalink' => get_permalink(),
				);
				$i++;
			}
			wp_reset_postdata();

			$result = array(
				'courses' => $courses,
				'count' => $courses_count,
			);
		}
		return $result;
	}
}


if ( ! function_exists( 'ilp_api_get_progress_by_mail' ) ) {

	function ilp_api_get_progress_by_mail($data){
		$mail=$_GET['mail']; //$data->get_param["mail"];
		$course_id=$_GET['course'];
		global $wpdb;

		$user=get_user_by("email",$mail);
		if($user !== false){
			$lp_user=learn_press_get_user( $user->ID );
			
			if($course_id==NULL){
				$all_courses=ilp_api_get_all_courses($data);
			}else{
				$all_courses=array('courses' => array(array('id' => intval($course_id))));
			}
			
			$progress=array();
			$i=0;
			if($all_courses!=NULL && $all_courses['courses']!=null){
				foreach($all_courses['courses'] as $course){
					if($lp_user->has_enrolled_course($course['id'])){
						$lp_course=learn_press_get_course( $course['id'] );
						$course_data       = $lp_user->get_course_data($course['id']);
						$course_results    = $course_data->get_results( false );
						$progress[$i]=array(
							'id' => $course['id'],
							'name' => $lp_course->get_title(), //$course['name'],
							'condition' => $lp_course->get_passing_condition(),
							'completed' => $course_results['completed_items'],
							'total' => $course_results['count_items'],
							'progress' => absint( $course_results['completed_items'] / $course_results['count_items'] * 100 ),
							'permalink' => $lp_course->get_permalink(),
						);
						$i++;
					}
				}
			}
			$result = array(
				'userfound' => true,
				'user_id' => $user->ID,
				'connect' => get_h5p_final(),
				'courses_progress' => $progress,
				'course_id' => $all_courses,
			);
			
			//$courses=$lp_user->get('enrolled-courses', array( 'status' => 'enrolled', 'limit' => 10 ));
			//$courses=learn_press_get_enrolled_courses($user->ID);
			
			
			
			//LP()->wp_query->have_posts()
			//! $user->has_enrolled_course( $course->get_id() )
			
		}else{
			$result = array(
				'userfound' => false,
				'mail' => $mail,
			);
		}
		return $result;
	}
}

if ( ! function_exists( 'ilp_api_get_course_content' ) ) {

	function ilp_api_get_course_content($data){
		$mail=$_GET['mail']; //$data->get_param["mail"];
		$course_id=intval($_GET['course']);
		$verif = "ouiiiiiiii";

		$user=get_user_by("email",$mail);
		if($user !== false){
			$lp_user=learn_press_get_user( $user->ID );
			$course=learn_press_get_course( $course_id );
			
			$course_data = $lp_user->get_course_data( $course_id );
			$course_results    = $course_data->get_results( false );
//			}
			
			
			$course_content=array();
			$sections = $course->get_curriculum();
//			$sections = $course->get_sections();
			$i=0;
			foreach($sections as $section){
				//$course_data = $lp_user->get_course_data( $course_id );
				$course_content[$i]=array(
					'id' => $section->get_id(),
					'name' => $section->get_title(),
					'completed' => $course_data->get_completed_items( '', false, intval($section->get_id()) ),
					'results' => $course_data->get_results( false ),
					'total' => $section->count_items( '', false ),
					//'items' => $section->get_items_array(),
				);
				$items = $section->get_items();
				$section_items=array();
				$j=0;
				foreach($items as $item){
					$veriftype=$item->get_item_type();
						if($veriftype=="lp_quiz")
						{
					$section_items[$j]=array(
						'id'      => $item->get_id(),
						'type'    => $veriftype,
						'title'   => $item->get_title(),
						'permalink' => $item->get_permalink(),
						'status'  => $lp_user->get_item_status($item->get_id(), $course_id),
						//'status2' => $item_data['status'],
						'grade'   => $lp_user->get_item_grade( $item->get_id(), $course_id ),
						'completed' => $lp_user->has_completed_item($item->get_id(),$course_id),
						'result'  => $course_data->get_item_result($item->get_id()),
					);
						}
						else
						{
						$section_items[$j]=array(
						'id'      => $item->get_id(),
						'type'    => $veriftype,
						'title'   => $item->get_title(),
						'permalink' => $item->get_permalink(),
						'status'  => $lp_user->get_item_status($item->get_id(), $course_id),
						//'status2' => $item_data['status'],
						'grade'   => get_h5p_grades(get_h5p_ID($item->get_title()),$user->ID,true),
						'completed' => $lp_user->has_completed_item($item->get_id(),$course_id),
						'result'  => get_h5p_grades(get_h5p_ID($item->get_title()),$user->ID,false),
					);
				}
					$j++;
				}
				$course_content[$i]['items']=$section_items;
				$i++;
			}
			$result = array(
				'userfound' => true,
				'results' => $course_results,
				'ci'   => $ci,
				'sections' => $course_content,
			);
		}else{
			$result = array(
				'userfound' => false,
			);
		}
		return $result;
	}
}
	//return the grade of the H5P activity from it's ID and the ID of the current user
	//return an int
	 function get_h5p_grades($ID,$ID_use,$cond){
		global $wpdb;
		$errored=100;
		$grade = $wpdb->get_results(
			$wpdb->prepare('SELECT score FROM mci_h5p_results WHERE content_id = %d AND user_id = %d', $ID, $ID_use)
		);
		$var=$grade[0]->score;
		$var=intval($var);
		var_dump($var);

		if (!$cond){
			$max = $wpdb->get_results(
				$wpdb->prepare('SELECT max_score FROM mci_h5p_results WHERE content_id = %d AND user_id = %d', $ID, $ID_use)
			);
			$pass = $wpdb->get_results(
				$wpdb->prepare('SELECT parameters FROM mci_h5p_contents WHERE id = %d', $ID)
			);
			$var1=$max[0]->max_score;
			$var1=intval($var1);
			var_dump($var1);
			
			$passing=$pass[0]->parameters;
			$json=json_decode($passing,true);
			$test=$json['behaviour']['passPercentage'];
			var_dump($test);
			var_dump($var1);
			if($var1==0)
			{
				return 0;
			}
			else
			{
			$note=($var/$var1)*100;
			var_dump($note);
			$note= round($note);
			var_dump($note);
			if($note>=$test)
			{
				$state="passed";
				var_dump($state);
			}elseif($note>$test||$test==null){
				$state="failed";
				var_dump($state);
			}else{
				$state=false;
			}
			return $state;
			}
		}else{
		if($var==0){
			$var="failed";
		}
		return $var;
		}
	}
	function get_h5p_final(){
		global $wpdb;
		$ID=1;
		$pass = $wpdb->get_results(
			$wpdb->prepare('SELECT parameters FROM mci_h5p_contents WHERE id = %d', $ID)
		);
		$passing=$pass[0]->parameters;
		$json=json_decode($passing,true);
		$test=$json['behaviour']['passPercentage'];
		var_dump($test);

		return $var;
	}

	//return the ID of the H5P activity from it's title
	//return an int
	function get_h5p_ID($titled){
		global $wpdb;
		//watch out for special characters like '
		$titled =str_replace('&#8217;','&#039;',$titled);
		$h5p_ID = $wpdb->get_results(
			$wpdb->prepare('SELECT id FROM mci_h5p_contents WHERE title = %s', $titled)
		);

		var_dump($titled);
		$var=$h5p_ID[0]->id;
		$var=intval($var);
		//var_dump($var);
		return $var;
		
	}

