<?php
// namespace DebugSizov\Debug;
session_start();
class LogDebug
{
	private $socetip = '45.80.70.119';
	private $socetPort = 24792;
	private $progectName = "VkMiniappBackend";
	private $version = "2.2";

	// config ELK
	private $log_ELK_on_off = "on";			 // Отключение логирования ELR
	private $file_ELK_print_on_off = "on";   // Отключение логирования ELR в фал
	private $socet_ELK_print_on_off = "on";  // Отключение логирования ELR по сети

	// Variable
	private	$Session;
	private	$construct;
	private $step;
	private $marker;

	private $step2;
	private	$Session2;

	// old Variable
	private $number;
	private $descript;
	private $data;



	/**
 	 * вызывается один раз на входной точке исследуемого скрипта
	 * @param string $markername
	 */
	public function stepMarker($markername="ALL"){


		$filename = dirname(__FILE__).'/logConfig.json';
		$logConfig = json_decode(file_get_contents($filename), true);
		$data = json_decode(json_encode($logConfig), true);

		$data["Session"]++;
		// старая версия

		$data[getmypid()] = null;
		$data[getmypid()]= [
			"step"=>0,
			"Session" => $data["Session"],
			"point" => getmypid(),
			"markername" => $markername
		];

		$data["step"] = 0;

		$this->Session2=$data[getmypid()]["Session"];
		$this->marker=$data[getmypid()]["markername"];
		$this->Session=$data["Session"];

		$fp = fopen($filename, 'wb+');
		ftruncate($fp, 0); // очищаем файл до 0 байтов.
		fclose($fp);
		unset($fp);
		$data = json_encode($data);
		$dh = fopen ($filename, 'ab+');
		fwrite($dh, $data);
		fclose($dh);
	}
	public function logStepping($marker){



		$this->construct++;
		$filename = dirname(__FILE__).'/logConfig.json';
		$logConfig = json_decode(file_get_contents($filename), true);
		$data = json_decode(json_encode($logConfig), true);

		$data[getmypid()]= [
			"step"=>$data[getmypid()]["step"]+ 1,
			"Session" => $data[getmypid()]["Session"],
			"point" => getmypid(),
			"markername" => $data[getmypid()]["markername"]
		];

		$this->step = $data[getmypid()]["step"];

		$fp = fopen($filename, 'wb+');
		ftruncate($fp, 0); // очищаем файл до 0 байтов.
		fclose($fp);
		unset($fp);
		$data = json_encode($data);
		$dh = fopen ($filename, 'ab+');
		fwrite($dh, $data);
		fclose($dh);


	}
	public function log_ELK($step=1, $namestep="Точка без параметров", $return="Готово!")
	{
		if ($this->log_ELK_on_off === 'on') {

			$filename = dirname(__FILE__).'/logConfig.json';
			$logConfig = json_decode(file_get_contents($filename));
			$data = json_decode(json_encode($logConfig), true);

			$debug_backtrace = debug_backtrace();
			$str = $debug_backtrace[0]["file"];
			$matches = array();
			preg_match('/([^\/]+$)/', $str, $matches);

			$this->Session=$data["Session"];



			//Проект
			//Файл
			//Строка
			//Дата
			//Время
			//Шаг    		- > Номер шага
			//Имя шага 	    - > что делает
			//return   		- > что возвращает


			// Обнуляем при старт поинте
			// Привязывааем к поинту
			//



			$this->logStepping($this->marker);
			$this->step++;


			$x=count($debug_backtrace);
			$indent = null;
			for ($i = 0; $i < $x; $i++){

				$str = $debug_backtrace[$i]["file"];
				$matches = array();
				preg_match('/([^\/]+$)/', $str, $matches);

				$working_way .= $indent.$matches[0].":".$debug_backtrace[$i]["line"]."\n ".$debug_backtrace[$i]["type"]. $debug_backtrace[$i]["function"]."()" ;
				$working_way .= "\n==============\n";
			}

			$testData = [
				"autoSession" => $data[getmypid()]["step"] + 1,
				"AS#" => $data[getmypid()]["step"] + 1,
				"pointMarker" => $data[getmypid()]["markername"],
				"point"=>getmypid(),
				"construct"=>$this->construct,
				"Session" => $data[getmypid()]["Session"],

				"dataStepSession2"=> json_encode($data,  JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
				"working_way" =>$working_way,
				"keyRandome" => $this->getRandString(4),
				"version" => $this->version,
				"progectName" => $this->progectName,
				"host" => $_SERVER["SERVER_NAME"],
				"script_file_path" => $debug_backtrace[0]["file"],
				"script_file" => $matches[0],
				"line" => $debug_backtrace[0]["line"],
				"short_message" => $namestep,
				"connect_server_port" => $this->socetPort,
				"debug_backtrace" => (string)json_encode($debug_backtrace, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
				"date" => date('Y-m-d'),
				"time" => date('H:i:s', time() - date('H:i:s')),
				"step" => $step,
				"full_message" => (string)json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
			];


			$message = (string)json_encode($testData);



			$this->printSocet($message);
			$this->printFile($testData);


		}
	}
	private function printFile($data)
	{
		if ($this->file_ELK_print_on_off === 'on') {

			file_put_contents(__DIR__ . '/log-data.txt', "\nData  =  \n" . print_r($data, true), FILE_APPEND);
		}
	}
	private	function getRandString($length, $alphabet = '1234567890qwertyuiopasdfghjklzxcvbnm')
	{
		$alphabet = str_repeat($alphabet, (int)($length / mb_strlen($alphabet)) + 1);
		return mb_substr(str_shuffle($alphabet), 0, $length);


	}
	private function printSocet($message){
		if ($this->socet_ELK_print_on_off === "on"){

			if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
				$bytesent = socket_sendto($socket, $message, strlen($message), 0, $this->socetip, $this->socetPort);
				socket_close($socket);
			} else {
				print("can't create socket\n");
			}
		}
		$this->printFile($bytesent);
	}






	// OLD








	public function log_file_print($nunber, $descript, $data)
	{
		$this->number = $nunber;
		$this->descript = $descript;
		$this->data = $data;

		if ($this->file_ELK_print_on_off === 'on') {
			$this->printLog("Загрузка");
		}
	}
	public function log_file_print_xml($nunber, $descript, $data)
	{
		if ($this->file_ELK_print_on_off === 'on') {
			$data = $data;  // JSON формат сохраняемого значения.
			$filename = dirname(__FILE__) . '/log-data.txt'; //
			$dh = fopen($filename, 'a+');
			fwrite($dh, "\n" . "\n" . "$nunber " . date('Y-m-d') . "    " . date('H:i:s', time() - date('H:i:s')) . "  " . $descript . "\n" . $data);
			fclose($dh);
		}
	}
	private function printLog($dataOptions)
	{
		$data = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);  // JSON формат сохраняемого значения.
		$filename = dirname(__FILE__) . '/log-data.txt';
		$dh = fopen($filename, 'a+');
		fwrite($dh, "\n" . "\n" . "$this->number  " . "$dataOptions" . date('Y-m-d') . "    " . date('H:i:s', time() - date('H:i:s')) . "  " . $this->descript . "\n" . $data);
		fclose($dh);
	}
}



