<?php 

namespace Appitized\CSVGenerator;
use Response;

class CSVGenerator {
	protected $csv_header = array();
	protected $csv_fields = array();
	protected $csv = array();
	protected $filename;
	protected $folder;
	protected $table;

	public function __construct($table = null)
	{
		if(isset($table)) $this->saveTable($table);
	}

	public function saveTable($table)
	{
		$this->table = $table;
		$this->setHeader($this->getTableDefinition());
		$this->setFields($this->getData());
		$this->setFilename($this->table);

		return $this;
	}

	public function getData()
	{
		$data = \DB::table($this->table)->get();

		return $data;
	}

	public function getTableDefinition()
	{
		$result = \DB::connection()->getPdo()->query("DESCRIBE " . $this->table)->fetchAll();
		foreach($result as $field)
		{	
			$definition[] = $field['Field'];
		}

		return $definition;
	}

	public function setHeader($headers) {
		foreach($headers as $header):
			$this->csv_header[0][] = $header;
		endforeach;
	}

	public function setFields($data) {
		$i = 0;
		foreach($data as $row):
			$row = array_values((array)$row);
			for($x = 0; $x < count($row); $x++) {
				$this->csv_fields[$i][$x] = $row[$x];  	
			}
			$i++;
		endforeach;
	}

	public function setFolder($folder) {
		$this->folder = $folder;

		return $this;
	}

	public function setFilename($filename) {
		$this->filename = strip_tags($filename) . '.csv';
	}

	private function setCSVFile() {
		$this->csv = array_merge($this->csv_header, $this->csv_fields);
		$this->unloadCSVAssets();
		return $this;
	}
	/**
	* Function to reduce memory load
	*/
	private function unloadCSVAssets() {
		$this->csv_header = array();
		$this->csv_fields = array();
	}

	public function saveCSV() {
		$this->setCSVFile();

		$CSVFile = $this->folder . '/' . $this->filename;

		$FileHandle = fopen($CSVFile, 'w') or die("ERROR: Cannot open file ".$CSVFile);
		fclose($FileHandle);

		$fp = fopen($CSVFile, 'w');
		foreach ($this->csv as $fields) {
			fputcsv($fp, $fields);
		}
		fclose($fp);
	}

	public function downloadCSV() 
	{
		$file = $this->folder . '/' . $this->filename;
		
		return Response::download($file);
  }
}