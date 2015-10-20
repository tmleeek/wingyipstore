<?php

class auto_deploy {
	const SSH_KEY_PATH = '~/.ssh/ecommage_git';
	const WEBSITE_PATH = '~/public_html';
	const AUTHENTICATION_KEY = '3sdjtSh6vAVMAYka5ywrxkUM2jAFDj2x';

	public function validateAuthentication() {
		if (isset($_GET['authen_key'])) {
			return $_GET['authen_key'] == self::AUTHENTICATION_KEY;
		}
		
		return false;
	}

	public function getSshKeyPath() {
		if (isset($_GET['ssh_key_path'])) {
			return $_GET['ssh_key_path'];
		}

		return self::SSH_KEY_PATH;
	}

	public function getWebsitePath() {
		if (isset($_GET['source_path'])) {
			return $_GET['source_path'];
		}

		return self::WEBSITE_PATH;
	}

	public function getShScriptPath() {
		if (isset($_GET['sh_script_path'])) {
			return $_GET['sh_script_path'] . '/auto-deploy.sh';
		}

		return getcwd() . '/auto-deploy.sh';
	}

	public function deploy() {
		if (!$this->validateAuthentication()) {
			return false;
		}

		shell_exec('sh ' . $this->getShScriptPath() . ' ' . $this->getSshKeyPath() . ' ' . $this->getWebsitePath());
	}
}

$autoDeploy = new auto_deploy();
$autoDeploy->deploy();