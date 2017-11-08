<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Card_model extends CI_Model {

	public $title;
	public $details;
	public $user_id;
	public $color;
	public $status;
	public $created_by;
	public $created_at;
	public $updated_at;
	

	# Get card By ID
	public function get($id) {
		
		$card 				  = $this->db->get_where('cards', ['id' => $id], 1)->result()[0];
		$card->comments		  = $this->get_card_comments($card->id);
		$card->viewers 		  = $this->get_card_viewers($card->id);
		$card->tags 		  = $this->get_card_tags($card->id);

		return $card;
	}


	# Get All card
	public function get_all($author_id, $status = null) {

		if($status != null)
			$cards = $this->db->get_where('cards', ['user_id' => $author_id, 'status' => $status])->result();
		else
			$cards = $this->db->get_where('cards', ['user_id' => $author_id])->result();

		foreach ($cards as $card) {

			$card->comments 	  = $this->get_card_comments($card->id);
			$card->viewers 		  = $this->get_card_viewers($card->id);
			$card->tags 		  = $this->get_card_tags($card->id);
		}
		
		return $cards;
	}


	# @param $order_by = column name
	# @param $direction = asc/desc
	public function order_by($order_by = 'created_at', $direction = 'asc') {

		return $this->db->order_by($order_by, $direction);
	}


	# Get card viewers
	public function get_card_viewers($id = null) {
		
		return $this->db->select('*')
			->from('users')
			->join('cards_assignment', 'cards_assignment.user_id = users.id')
			->where('cards_assignment.card_id', $id)
			->get()
			->result();
	}


	# Get card Tags
	public function get_card_tags($id = null) {

		return $this->db->select('name')
			->from('tags')
			->join('cards_tagging', 'cards_tagging.tag_id = tags.id')
			->where('cards_tagging.card_id', $id)
			->get()
			->result();
	}


	# Add card comments
	public function add_card_comments($card_id, $comment_details) {

		$this->db->insert('card_comments', $comment_details);
	}


	# Get card comments
	public function get_card_comments($card_id) {

		return $this->db->get_where('card_comments', ['card_id' => $card_id])->result();
	}


	# Add card returning ID
	public function insert($card_details) {

		$card_details['status'] = 1;
		$card_details['created_at'] = date('Y-m-d');
		$card_details['updated_at'] = date('Y-m-d');

		$this->db->insert('cards', $card_details);

		return $this->db->insert_id();
	}


	# FOR KANBAN BOARD STATUS UPDATE
	public function update_status($id, $key) {

		return $this->db->update('cards', ['status' => $key, 'completion_date' => date('Y-m-d')], "id = $id");
	}


	public function update($id, $card_details) {

		return $this->db->update('cards', $card_details, "id = $id");
	}


	public function prune_cards($id) {
		
		foreach($this->db->get_where('cards', ['user_id' => $id])->result() as $card){
			
			$this->db->delete('card_comments', 	['card_id' => $card->id]);
			$this->db->delete('cards_tagging',  ['card_id' => $card->id]);
		}

		$this->db->delete('cards', ['user_id' => $id]);
	}
	

	public function add_viewers($card_id, $users) {
		
		if(count($users) == 0)

			$new_member_ids = [];
		else

			$new_member_ids = array_column($this->db->select('id')->from('users')->where_in('email_address', $users)->get()->result_array(), 'id');
			
		$old_member_ids = array_column($this->db->select('user_id')->from('cards_assignment')->where('card_id', $card_id)->get()->result_array(), 'user_id');

		foreach ($new_member_ids as $id) {
			
			if(!in_array($id, $old_member_ids)) {
				
				$this->db->insert('cards_assignment', [
					'card_id' => $card_id,
					'user_id' => $id
				]);
			}
		}

		foreach ($old_member_ids as $id) {
			
			if(!in_array($id, $new_member_ids)) {
			
				$this->db->delete('cards_assignment', [
					'card_id' => $card_id,
					'user_id' => $id
				]);
			}
		}
	}
}