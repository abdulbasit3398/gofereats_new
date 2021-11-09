<?php

/**
 * Review Model
 *
 * @package    GoferEats
 * @subpackage Model
 * @category   Review
 * @author     Trioangle Product Team
 * @version    1.1
 * @link       http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class Review extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $table = 'review';

	public $typeArray = [
		'user_menu_item' => 0,
		'user_driver' => 1,
		'user_store' => 2,
		'store_delivery' => 3,
		'driver_delivery' => 4,
		'driver_store' => 5,
	];

	protected $appends = ['store_rating_count','user_to_driver_rating', 'store_rating', 'average_rating', 'star_rating'];

	/**
	 * To filter based on type text
	 */
	public function scopeTypeText($query, $type = "user_menu_item") {
		$type_value = $this->typeArray[$type];
		return $query->type($type_value);
	}

	/**
	 * @param  [type]
	 * @param  integer
	 * @return [type]
	 */
	public function scopeType($query, $type = 0) {
		return $query->where('type_id', $type);
	}

	public function review_issue($query) {
		return $this->hasMany('App\Models\ReviewIssue', 'review_id', 'id');
	}

	// Join with OrderItemModifier table
	public function issue() {
		return $this->hasMany('App\Models\ReviewIssue', 'review_id', 'id');
	}

	// Join with OrderItemModifier table
	public function get_issue() {
		return $this->hasMany('App\Models\ReviewIssue', 'review_id', 'id');
	}


	/**
	 * User to driver rating  //user_driver_rating
	 */
	public function getUserDriverRatingAttribute() {
		$review = Review::where('reviewee_id', $this->attributes['reviewee_id'])->where('type', $this->typeArray['user_driver'])->get();
		if ($review) {
			$is_thumbs = $review->sum('is_thumbs');
			$count = $review->count();
			if ($is_thumbs != 0) {
				return (string) round($is_thumbs * 5 / $count);
			} else {
				return (string) 0;
			}

		} else {

			return (string) 0;
		}
	}

	public function getUserToDriverRatingAttribute() {
		$review = Review::where('reviewee_id', $this->attributes['reviewee_id'])->where('type', $this->typeArray['user_driver'])->first();
		if ($review) {
				return $review;
			}		
		else {
			return (string) 0;
		}
	}

	public function getUserToDriverCommentsAttribute() {
		$comments  = DB::table('review')
			->leftjoin('review_issue', 'review_issue.review_id', '=', 'review.id')
			->leftjoin('issue_type', 'review_issue.issue_id', '=', 'issue_type.id')
			->select(DB::raw('GROUP_CONCAT(issue_type.name )as issues'),'comments')
			->where('order_id',$this->order_id)
			->where('review.type',1)
			->groupBy('review.id')
			->first();
		if ($comments) {
				return $comments;
			}		
		else {
			return '';
		}	
	}

	/**
	 * User to Store rating
	 */
	public function getStoreRatingAttribute() {
		$review = Review::where('reviewee_id', $this->reviewee_id)->where('type', $this->typeArray['user_store'])->get();
		if ($review) {
			$rating = $review->sum('rating');
			$count = $review->count();
			if ($rating != 0) {
				return number_format(($rating / $count), 1);
			} else {
				return (string) 0;
			}

		} else {

			return (string) 0;
		}
	}

	/**
	 * User to Store rating count
	 * store_rating_count
	 */
	public function getStoreRatingCountAttribute() {
		$review = Review::where('reviewee_id', $this->reviewee_id)->where('type', $this->typeArray['user_store'])->get();
		if ($review) 
			return  $review->count();
		else
			return  0;
	}

	/**
	 * User to Average Store rating
	 */
	public function getAverageRatingAttribute() {
		$review = Review::where('reviewee_id', $this->reviewee_id)->where('type', $this->typeArray['user_store'])->get();
		if ($review) {
			return (string) $review->count();

		} else {

			return (string) 0;
		}
	}

	/**
	 * User to Store star individual order
	 */
	public function getStarRatingAttribute() {

		$review = Review::where('order_id', $this->order_id)->where('reviewee_id', $this->reviewee_id)->where('type', $this->typeArray['user_store'])->first();
		if ($review) {
			return (string) $review->rating;
		} else {
			return '0.0';
		}

	}

	/**
	 * User to Store star individual order
	 */
	public function getUserAtleastAttribute() {

		$review = Review::where('order_id', $this->order_id)->whereIn('type', [$this->typeArray['user_store'], $this->typeArray['user_menu_item'], $this->typeArray['user_driver']])->get();

		if (count($review) > 0) {
			return 1;
		} else {
			return 0;
		}

	}

	public function getReviewTypeAttribute() {
		$type_value	 = array_search($this->type,$this->typeArray);
		return $type_value;
	}


	public function getReviewCommentsAttribute()
	{
		$review = Review::where('order_id', $this->order_id)->leftjoin('review_issue', 'review_issue.review_id', '=', 'review.id')->leftjoin('issue_type', 'review_issue.issue_id', '=', 'issue_type.id')->where('reviewee_id',$this->reviewee_id)->where('review.type',0)->groupBy('review.id')->select(DB::raw('GROUP_CONCAT(issue_type.name )as issues'),'comments')->first();	
		if(isset($review ))
			return $review;
		else
			return '' ;  
	}

	public function getDriverDeliveryRatingAttribute()
	{
		$review = Review::where('order_id', $this->order_id)->where('reviewee_id',$this->reviewee_id)->whereIn('type', [ $this->typeArray['driver_delivery']])->select('is_thumbs','comments')->first();	
		if(isset($review))
			return $review->is_thumbs;
		else
			return '';
	}	

	public function getStoreDeliveryRatingAttribute()
	{
		
		$review = Review::where('order_id', $this->order_id)->where('reviewee_id',$this->reviewee_id)->whereIn('type', [ $this->typeArray['store_delivery']])->first();	
		// dd($review);
		if(isset($review)){
			return $review;
		}

		else
			return '';
	}	

	public function getStoreDeliveryCommentsAttribute() {
		$comments  = DB::table('review')
			->leftjoin('review_issue', 'review_issue.review_id', '=', 'review.id')
			->leftjoin('issue_type', 'review_issue.issue_id', '=', 'issue_type.id')
			->select(DB::raw('GROUP_CONCAT(issue_type.name )as issues'),'comments')
			->where('order_id',$this->order_id)
			->where('review.type',3)
			->groupBy('review.id')
			->first();
		if ($comments) {
				return $comments;
			}		
		else {
			return '';
		}	
	}

	public function getDriverDeliveryCommentsAttribute()
	{

		$comments  = DB::table('review')
			->leftjoin('review_issue', 'review_issue.review_id', '=', 'review.id')
			->leftjoin('issue_type', 'review_issue.issue_id', '=', 'issue_type.id')
			->select(DB::raw('GROUP_CONCAT(issue_type.name )as issues'),'comments')
			->where('order_id',$this->order_id)
			->where('review.type',4)
			->groupBy('review.id')
			->first();
		if(isset($comments))
			return $comments;
		else
			return '';
	}	

}
