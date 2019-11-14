<?php
namespace Wamania\BrewSearch\Catalog\Dictionary;

use Wamania\BrewSearch\Catalog\CatalogConst as CC;
use Wamania\BrewSearch\Utils\Utils;
use Wamania\BrewSearch\Catalog\Dictionary\Stage\Stage;
use Wamania\BrewSearch\Catalog\Dictionary\Stage\StagePartFormater;
use Wamania\BrewSearch\File\PhysicalFile;
use Wamania\BrewSearch\File\MemoryFile;
use Wamania\BrewSearch\File\File;

class SwitchBoard
{
	/**
	 * We work on loaded content or directly on the file ?
	 * @var boolean
	 */
	protected $isLoaded;

	/**
	 * Instance of File (PhysicalFile or MemoryFile)
	 */
	protected $file;

	protected $options;

	/**
	 * Constructor
	 * @param string $filePath
	 */
	public function __construct($filePath, $options)
	{
	    $this->options = array_merge($options, array('file' => 'memory'));

	    $this->file = File::factory($this->options['file'], $filePath);

		//$this->file = new MemoryFile($filePath);

		$this->isLoaded = false;
	}

	/**
	 * Init function
	 * @return void
	 */
	public function init()
	{
		$this->file->init();
		$this->load();

		$filesize = $this->file->getFilesize();
		if ($filesize < (CC::ANNUAIRE_SIZE_BYTES + CC::ID_BYTES + CC::NEXT_PART_BYTES)) {
			$this->file->writeBytes(Utils::pack(0, CC::ANNUAIRE_SIZE_BYTES));
			$this->file->writeBytes(Utils::pack(0, CC::ID_BYTES));
			$this->file->writeBytes(Utils::pack(0, CC::NEXT_PART_BYTES));

			$this->file->flush();
		}
	}

	public function load()
	{
	    if (! $this->isLoaded) {
	        $this->file->open();
	        $this->isLoaded = true;
	    }
	}

	public function scan($chars)
	{
	    $lastFound = $this->scanIndex(0, $chars, 0);

	    // if switchboard is empty
	    if (null == $lastFound) {
	        $lastFound = array(
	            'charsIndex' => 0,
	            'index' => 0
	        );
	    }

	    return $lastFound;
	}

	/**
	 * On cherche récursivement tous les lettres du mot
	 *
	 * @param  int $index 	        Position dans le fichier
	 * @param  array $chars       	Tableau des lettres du mot
	 * @param  int $charsIndex   	Position dans le tableau de letters
	 * @return array 				Dernier position trouvée
	 */
	public function scanIndex($index, $chars, $charsIndex)
	{
	    // on est allé trop loin, c'est qu'on a tout trouvé
	    if ($charsIndex == count($chars)) {
	        return array(
	            'charsIndex' => $charsIndex,
	            'index' => $index
	        );
	    }

	    $stage = new Stage($this, $index);
	    $annuaire = $stage->getAnnuaire();
	    $found = null;

	    foreach ($annuaire as $letter => $position) {

	        // on a notre lettre
	        if ($letter == $chars[$charsIndex]) {
	            //echo "Letter : ".pack('C*', $letter)." => ".$position."\n";
	            $found = $this->scanIndex($position, $chars, $charsIndex+1);

	            if (null === $found) {
	                $found = array(
	                    'charsIndex' => ($charsIndex+1),
	                    'index' => $position
	                );
	                break;
	            }
	        }
	    }

	    return $found;
	}

	public function readIndex($index = 0)
	{
        $stage = new Stage($this, $index);
        $parts = $stage->getParts();

        return $parts;
	}

	public function extract($start, $length)
	{
	    $this->file->seek($start);
	    return $this->file->readBytes($length);
	}

	public function lastIndex()
	{
	    $this->file->seekToEnd();
	    return $this->file->getPosition();
	}

	/*public function getLastId()
	{

	}*/

	public function add(StagePartFormater $stagePartFormater)
	{
	    $this->file->writeAtEnd($stagePartFormater);
	}

	public function replace($replace, $start)
	{
	    $this->file->seek($start);
	    $this->file->writeBytes($replace);
	}

	public function flush()
	{
		return $this->file->flush();
	}
}