<?php
namespace Wamania\BrewSearch\Catalog\Dictionary;

use Wamania\BrewSearch\Catalog\Catalog;
use Wamania\BrewSearch\Catalog\Dictionary\Stage\StagePartFormater;
use Wamania\BrewSearch\Catalog\Dictionary\Stage\Stage;
use Wamania\BrewSearch\Catalog\CatalogConst as CC;
use Wamania\BrewSearch\Catalog\Dictionary\SwitchBoard;
use Wamania\BrewSearch\Catalog\Dictionary\Id;
use Wamania\BrewSearch\Cache\Cache;

class Dictionary
{
	/**
	 * Current dictionary Id
	 * @var Wamania\BrewSearch\Catalog\Id
	 */
	protected $id;

	protected $options;

	protected $catalog;

	protected $cache;

	/**
	 * Our switchboard
	 * @var Wamania\BrewSearch\Catalog\SwitchBoard\SwitchBoard
	 */
	protected $switchBoard;

	/** (non-PHPdoc)
	 * @see \Wamania\BrewSearch\Catalog\Brew\Brew::__construct()
	 */
	public function __construct(Catalog $catalog, $options)
	{
		$this->catalog    = $catalog;
		$this->options    = $options;

		$catalogPath = $catalog->getPath();

		$this->switchBoard = new SwitchBoard(
		    $catalogPath . DIRECTORY_SEPARATOR
		    . 'dictionary' . DIRECTORY_SEPARATOR
		    . 'switchboard.bin', $options);

		$config = $catalog->getConfig();

		if (isset($config['cache'])) {
		    $this->cache = Cache::factory(array_merge($config['cache'], array(
		        'path' => $catalogPath . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'dictionary',
		        'name' => 'dictionary'
		    )
		    ));
		}

	    $this->id = new Id(
	        $catalogPath . DIRECTORY_SEPARATOR
	        . 'dictionary' . DIRECTORY_SEPARATOR
	        . 'autoincrement.bin', $options);
	}

	public function init()
	{
	    $catalogPath = $this->catalog->getPath();

	    if (!is_dir($catalogPath . DIRECTORY_SEPARATOR . 'dictionary')) {
	        mkdir($catalogPath . DIRECTORY_SEPARATOR . 'dictionary', 0777, true);
	    }

	    if (null != $this->cache) {
	        $this->cache->init();
	    }

	    $this->switchBoard->init();
	    $this->id->init();
	}

	public function load()
	{
	    $this->switchBoard->load();
	    $this->id->load();
	}

	public function search($word)
	{
	    $chars = unpack('C*', $word);
	    // we reindex to begun at index 0
	    $chars = array_values($chars);

	    $lastFound = $this->switchBoard->scan($chars);

	    // word found
	    if ($lastFound['charsIndex'] == count($chars)) {
	        $stage = new Stage($this->switchBoard, $lastFound['index']);
	        $id = $stage->getFirstPart()->getId();

	        return $id;
	    }

	    return null;
	}

	/**
	 * First step, we add the word
	 *
	 * @param text $word
	 * @param integer $idDocument
	 */
	public function indexWord($word)
	{
	    // we use cache
	    if (null != $this->cache) {
	        $id = $this->cache->get($word);

	        if (null != $id) {
	            return $id;
	        }
	    }

	    $chars = unpack('C*', $word);
	    $chars = array_values($chars);

	    if ( (isset($this->options['min_length'])) && (count($chars) < $this->options['min_length']) ) {
            return null;
	    }

	    // pour chaque chars, on va checker s'il est déjà dedans
	    // lastFound va contenir la position du dernier stage trouvé pour le mot et la position dans $chars
	    //$start = microtime(1);
	    $lastFound = $this->switchBoard->scan($chars);

	    if ($lastFound['charsIndex'] == count($chars)) {

	        // on charge le Stage qu'on vient de trouver
	        $stage = new Stage($this->switchBoard, $lastFound['index']);

	        // enfin l'id du 1er StagePart
	        $id = (int)$stage->getFirstPart()->getId();

	        if (null != $this->cache) {
	            $this->cache->set($word, $id);
	        }

	    // sinon, reste des stages à insérer
	    } else {

	        // Un 1ier stagepart pour compléter l'annuaire de la dernière lettre trouvée
	        // le stage existe déjà, on a donc déjà un 1er StagePart avec un id
	        $stage = new Stage($this->switchBoard, $lastFound['index']);
	        $stage->addPart(array(
	            $chars[$lastFound['charsIndex']] => ($this->switchBoard->lastIndex() + CC::FULL_STAGE_PART_SIZE)
	        ), 0);

	        // tous les nouveaux Stage et pour chacun une StagePart, à la fin et avec un id
	        $stage = new Stage($this->switchBoard);
	        for ($i=($lastFound['charsIndex']+1); $i < count($chars); $i++) {

	            // le dernier Id +1
	            $id = $this->id->increment();

	            $stage->addPart(array(
	                $chars[$i] => ($this->switchBoard->lastIndex() + CC::FULL_STAGE_PART_SIZE)
	            ), $id);
	        }

	        // le dernier Id +1
	        $id = (int)$this->id->increment();
	        //$this->id->flush();

	        // le dernier stage, ne contient qu'un id
	        $stage->addPart(null, $id);

	        if (null != $this->cache) {
	            $this->cache->set($word, $id);
	        }
	    }

	    return $id;
	}

	public function flush()
	{
	    $this->switchBoard->flush();
	    $this->id->flush();
	}
}