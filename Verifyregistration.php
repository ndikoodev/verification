<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class VerifyRegistration extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
		
		
        $this->load->helper(array('form'));
        $this->load->helper('security');
        $this->load->library('form_validation');

        // $this->form_validation->set_rules('reg-name', 'Name', 'required|min_length[6]|max_length[50]|is_unique[users.username]');
        // $this->form_validation->set_rules('reg-password', 'Password', 'required|matches[reg-conf-password]');
        // $this->form_validation->set_rules('reg-conf-password', 'Repeat Password', 'required');
        // $this->form_validation->set_rules('reg-email', 'Email', 'required|valid_email|is_unique[users.email]');

        $this->form_validation->set_rules('reg-name', 'Name', 'required');
        $this->form_validation->set_rules('reg-password', 'Password', 'required|min_length[5]|max_length[50]');
        $this->form_validation->set_rules('reg-conf-password', 'Repeat Password', 'required');
        $this->form_validation->set_rules('reg-email', 'Email', 'required|callback_create_user');

		$this->load->model("settings_model");
		$data['getSettings'] = $this->settings_model->getSettings();
        if ($this->form_validation->run() == TRUE) {
            // $this->load->view('myform');
            $this->load->view('register_view',$data);
        } else {
            redirect('login', 'refresh');
        }
   }

    public function create_user($str) {
        $email = $this->input->post('reg-email');
        $pwd = $this->input->post('reg-password');
        $name = $this->input->post('reg-name');

        $flag = $this->aauth->create_user($email, $pwd, $name);

        if ($flag) {
			
			//send verfication to the user
			//$this->aauth->send_verification($flag);
			$this->load->model('settings_model');
			$getSettings = $this->settings_model->getSettings();
			
			$this->load->library('email');
			$this->email->from($getSettings[0]->sender_email, $getSettings[0]->support_email);
			$this->email->to($email);
			$this->email->subject($getSettings[0]->register_subject_line);
			$this->email->message($getSettings[0]->email_text);
			$this->email->send();
			
			return true;
			
        } else {
            $error_msg = $this->aauth->print_errors();
            // echo $error_msg;
            // $this->form_validation->set_message('create_user', 'Account already exists on the system with that username/email.');
            $this->form_validation->set_message('create_user', $error_msg);
            return false;
        }


        // print_r($this->aauth->get_user($a));

    }
	
	public function verification($userID, $verificationCode)
	{
		$verifyUser = $this->aauth->verify_user($userID, $verificationCode);
		if($verifyUser == true)
		{
			redirect('campaignlist', 'refresh');
			exit();
			//$this->aauth->login('admin@admin.com', 'password');
		}
		else
		{
			redirect('login', 'refresh');
			exit();
		}
	}

}
