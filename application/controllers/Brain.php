<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Brain extends CI_Controller
{
	public function accueil()
	{
		//appel du model
		$this->load->model('Products_model');
		//récupération des résultat de la method accueil
		$result = $this->Products_model->products_select();

		
		$view['list'] = $result;

		

		if ($this->input->post())
		{
			$this->Ranks_model->accueil_insert();
			redirect("brain/accueil");
		}
		else
		{
			$this->my_header->set_header();
			$this->load->view('accueil', $view);
			$this->load->view('footer');
		}
	}

	public function products($id)
	{
		//$this->output->enable_profiler(TRUE);
		$this->load->model('Products_model');
		$result = $this->Products_model->products_type($id);
		$view['list'] = $result;

		$this->load->model('Category_model');
		$result = $this->Category_model->category_select_u($id);
		$view['categ'] = $result;



		$this->my_header->set_header();
		$this->load->view('products', $view);
		$this->load->view('footer');
	}

	public function category()
	{
		//$this->output->enable_profiler(TRUE);
		$this->load->model('Category_model');
		$result = $this->Category_model->category_select();
		$view['list'] = $result;

		if ($this->input->post())
		{
			$this->Category_model->category_insert();
			redirect("brain/category");
		}
		else
		{
			$this->my_header->set_header();
			$this->load->view('category', $view);
			$this->load->view('footer');
		}
	}

	public function delete() 
	{
		//$this->output->enable_profiler(TRUE);	
		$id = $this->input->post('idH');
		$this->load->model('Category_model');
		$this->Category_model->category_delete($id);
		redirect("brain/category");
	}

	public function categ_modif($id)
	{
		//$this->output->enable_profiler(TRUE);	
		$this->load->model('Category_model');
		$result = $this->Category_model->category_select_u($id);
		$view['categ'] = $result;

		if ($this->input->post())
		{
			$this->Category_model->category_update($id);
			redirect("brain/category");
		}
		else
		{
			$this->my_header->set_header();
			$this->load->view('categ_modif', $view);
			$this->load->view('footer');	
		}
	}

	public function profile()
	{
		if ($this->session->loged)
		{
			$this->my_header->set_header();
			$this->load->view('profile');
			$this->load->view('footer');	
		}
		else
		{
			$this->my_header->set_header();
			$this->load->view('sign');
			$this->load->view('footer');	
		}
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('brain/accueil');	
	}

	public function signup()
	{
		if ($this->form_validation->run('signup') == FALSE)
        {
        	$data["signup"] = TRUE;
        	$this->my_header->set_header();
			$this->load->view('sign', $data);
			$this->load->view('footer');
        }
        else
        {
			// $this->output->enable_profiler(TRUE);
        	$data = $this->input->post();
			
			unset($data['user_passwordConfirm']);
			$signupDate = new Datetime();
			$data['user_password'] = password_hash($this->input->post('user_password'),PASSWORD_DEFAULT);
			$data['user_try'] = 1;
			$data['user_blocked'] = md5($this->input->post('user_login'));

			$this->load->model('Users_model');
			$this->Users_model->users_insert($data);

        	include 'application/views/signup_mail.php';
			$this->email->from('nepasrepondre@fougicrok.com');
			$this->email->to($this->input->post('user_mail'));
			$this->email->set_mailtype("html");
			$this->email->subject('Validation de votre E-mail');
			$this->email->message($message);
			$this->email->send();

			$msg['msg'] = 'Un mail d\'activation vient d\'être envoyé à '.$data['user_mail'].', consultez votre boite mail !';
			$this->my_header->set_header();
			$this->load->view('texted', $msg);
			$this->load->view('footer');
		}
	}

	public function reMail()
	{
		if ($this->form_validation->run('reMail') == FALSE && $this->input->post('mail') != $this->input->post('oriMail'))
        {
        	$msg['msg'] = 'Votre compte n\'est pas activé, vérifiez votre boite mail !';
    		$msg['reMail'] = TRUE;
    		$msg['mail'] = $this->input->post('mail');
    		$msg['blocked'] = $this->input->post('blocked');
    		$msg['login'] = $this->input->post('login');
        	$this->my_header->set_header();
			$this->load->view('texted', $msg);
			$this->load->view('footer');
        }
        else
        {
			$data['user_login'] = $this->input->post('login');
			$data['user_blocked'] = $this->input->post('blocked');

			$this->load->model('Users_model');
			$this->Users_model->users_update_mail($data['user_login'], $this->input->post('mail'));

			include 'application/views/signup_mail.php';
			$this->email->from('nepasrepondre@fougicrok.com');
			$this->email->to($this->input->post('mail'));
			$this->email->set_mailtype("html");
			$this->email->subject('Validation de votre E-mail');
			$this->email->message($message);
			$this->email->send();

			$msg['msg'] = 'Un mail d\'activation vient d\'être envoyé à '.$this->input->post('mail').', consultez votre boite mail !';
			$this->my_header->set_header();
			$this->load->view('texted', $msg);
			$this->load->view('footer');
		}

	}

	public function exist_login($login)
	{
		$this->load->model('Users_model');

		if($this->Users_model->users_select_u($login) != NULL)
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('exist_login', 'Ce login n\'existe pas');
			return FALSE;	
		}
	}

	public function signin()
	{
    	$login = $this->input->post('signin_login');
    	$this->load->model('Users_model');
		$result = $this->Users_model->users_select_u($login);

		if ($this->exist_login($login))
		{
			if ($result->user_blocked != NULL)
        	{
        		$msg['msg'] = 'Votre compte n\'est pas activé, vérifiez votre boite mail !';
        		$msg['reMail'] = TRUE;
        		$msg['mail'] = $result->user_mail;
        		$msg['blocked'] = $result->user_blocked;
        		$msg['login'] = $result->user_login;
        		$this->my_header->set_header();
				$this->load->view('texted', $msg);
				$this->load->view('footer');
        	}
        	else
        	{
        		if ($result->user_try >= 3)
        		{
        			$this->my_header->set_header();
					$this->load->view('password_lost', $result);
					$this->load->view('footer');
        		}
        		else
        		{
	        		if ($this->form_validation->run('signin') == FALSE)
			        {
			        	$data["signin"] = TRUE;
			        	$this->my_header->set_header();
						$this->load->view('sign', $data);
						$this->load->view('footer');
			        }
			        else
			        {
			        	$this->Users_model->users_date_log($login);
						$this->Users_model->user_try_reset($login);
					}
				}
        	}
        }
        else
        {
        	$data["spanLogin"] = 'Ce login n\'existe pas';
        	$data["signin"] = TRUE;
        	$this->my_header->set_header();
			$this->load->view('sign', $data);
			$this->load->view('footer');
        }
	}

	public function password_lost()
	{
		$this->load->model('Users_model');
		$result = $this->Users_model->users_select_u($this->input->post('login'));
		
		if ($this->form_validation->run('pwd_lost') == FALSE)
		{
			$this->my_header->set_header();
			$this->load->view('password_lost',$result);
			$this->load->view('footer');
		}
		else
		{
			if($this->input->post('anwser') != $result->user_answer)
			{
				$data["spanAnswer"] = 'La réponse ne correspond pas';
				$this->my_header->set_header();
				$this->load->view('password_lost',$data + $result);
				$this->load->view('footer');
			}
		}
	}
	

	public function password_verify($pwd)
	{
		$this->load->model('Users_model');
		$result = $this->Users_model->users_select_u($this->input->post('signin_login')); 

		if ($result != NULL)
		{
			if(password_verify($pwd, $result->user_password))
			{
				return TRUE;
			}
			else
			{
				$try = intval($result->user_try);
				$try++;
				$this->form_validation->set_message('password_verify', 'Mot de passe incorrect');
				$this->Users_model->user_try_plus($result->user_id, $try);
				return FALSE;	
			}
		}

	}

	public function mail_success($block)
	{
		$this->load->model('Users_model');
		$this->load->model('Ranks_model');
		$result = $this->Users_model->users_select_u_block($block);
		$this->Users_model->users_blocked_reset($block);
		$rankResult = $this->Ranks_model->ranks_select_u($result->rank_id);

		$this->session->loged = TRUE;
		$this->session->login = $result->user_login;
		$this->session->mail = $result->user_mail;
		$this->session->rank = $rankResult->rank_name;

		$msg['msg'] = 'Merci d\'avoir confirmé votre e-mail, votre compte est maintenant actif !';
		$this->my_header->set_header();
		$this->load->view('texted', $msg);
		$this->load->view('footer');	

		header ("Refresh: 3; URL=../accueil");
	}
}
?>

