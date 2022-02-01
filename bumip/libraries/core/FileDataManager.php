<?php
namespace Bumip\Core;

/**
 * EntityManager
 * lists all entities,
 * if you load an Entity it will be saved in Data.
 * It does filesystem manipulation, so it can be slow but it should be used only by admins/dev and not so often.
 */
class FileDataManager extends DataHolder
{
    private $config;
    private $noun = 'entity';
    private $nounPlural = 'entities';
    private $entities;
    private $directory;
    private $currentEntityName = null;
    private $currentEntityPath = null;

    public function __construct(DataHolder $conf, $directory =  'app/entities/')
    {
        $this->config = $conf;
        $this->directory = empty($this->config->get($this->nounPlural + "Directory")) ? $directory : $this->config->get($this->nounPlural + "Directory");
        //if has no trailing slash
        if (strpos($this->directory, '/', -1) != strlen($this->directory) - 1) {
            $this->directory .= '/';
        }
    }
    public function setDirectory($dir)
    {
        $this->directory = $dir;
        if (strpos($this->directory, '/', -1) != (strlen($this->directory) - 1)) {
            $this->directory .= '/';
        }
    }
    public function list($dir = null)
    {
        $dir = $dir ?? $this->directory;
        $dirContent = scandir($dir);
        $basePath = $dir;
        //Count 2 because first 2 elements of scandir are for directory navigation (eg. "." "..")
        if (count($dirContent) == 2) {
            return false;
        }
        /**
         * The file system can be organized like this:
         * entities/entityname.php a normal php model but is still recognized as entity.
         * entities/entityname.json this will be used with a standard base entity php class. a entities/schemas/entityname-schema.php will be generated as cache.
         * entities/entityname/schema.json it's used for 2 reasons: better organization or custom entity. A custom entity can be generated and a entities/entityname/schema.php will be generated.
         */
        foreach ($dirContent as $f) {
            if ($f == '..' || $f == '.') {
                //Skip
            } elseif (!is_dir($basePath . $f) && is_file($basePath . $f)) {
                $info = pathinfo($f);
                $name = ucfirst(basename($f, '.'.$info['extension']));
                $entity = ['name' => $name, "extention" => $info['extension'], 'path' => $basePath . $info['basename']];
                if (!empty($entities[$name])) {
                    $entities[$name] = array_merge($entities[$name], $entity);
                } else {
                    $entities[$name] = $entity;
                }
            } elseif ($f != 'schemas') {
                $currentPath = $basePath . $f . '/';
                $name = ucfirst($f);
                $entities[$name] = ['name' => $name, 'path' => $currentPath];
                $entities[$name]['files'] = $this->getFiles($f);
            }
        }
        $this->entities = $entities;
        return ($entities);
    }
    protected function getFiles($path, $isFullPath = false)
    {
        $currentPath = $this->directory . $path . '/';
        if ($isFullPath) {
            $currentPath = $path;
        }
        $files = [];
        foreach (scandir($currentPath) as $ff) {
            if ($ff == '..' || $ff == '.') {
                //Skip
            } elseif (!is_dir($currentPath . $ff)) {
                $info = pathinfo($ff);
                $file = [ "extention" => $info['extension'], 'path' => $currentPath . $info['basename']];
                $files[] = $file;
            }
        }
        return $files;
    }
    public function loadEntity(string $entityName)
    {
        //let's check if there is a directory with that name.
        $basePath = $this->directory;
        $uppercase = $basePath . \ucfirst($entityName) . '/';
        $hasDir[(int) is_dir($uppercase)] = $uppercase;
        $lowercase = $basePath . \strtolower($entityName) . '/';
        $hasDir[(int) is_dir($lowercase)] = $lowercase;
        if (!empty($hasDir[1])) {
            $entity = ['name' => \ucfirst($entityName), 'path' => $hasDir[1], 'isDirectory' => true];
            //is a directory entity.
            $entity['files'] = $this->getFiles($hasDir[1], true);
            return $entity ? new \Bumip\Core\DataHolder($entity) : false;
        } else {
            $entity = false;
            foreach (scandir($basePath) as $f) {
                if ($f == '..' || $f == '.') {
                    //Skip
                } elseif (!is_dir($basePath . $f) && is_file($basePath . $f) &&
                (strpos(strtolower($f), strtolower($entityName . '.php')) > -1 || strpos(strtolower($f), strtolower($entityName . '-')) > -1)) {
                    $info = pathinfo($f);
                    $name = ucfirst(basename($f, '.'.$info['extension']));
                    $entity = ['name' => $name, "extention" => $info['extension'], 'path' => $basePath . $info['basename'],  'isDirectory' => false];
                    if (!empty($entities[$name])) {
                        $entities[$name] = array_merge($entities[$name], $entity);
                    } else {
                        $entities[$name] = $entity;
                    }
                }
            }
            return $entity ? new \Bumip\Core\DataHolder($entity) : false;
        }
    }
    /**@todo check default json-schema type for php (assoc or object?) */
    private function generateEntityFiles($e)
    {
        //is valid json_string
        if (is_string($e) && strpos($e, '{') != -1) {
            $entity['json'] = ['type' => 'json', 'content' => $e];
            $ePhp = 'return ' . var_export(\json_decode($e, true), true) . ';';
            $entity['php'] = ['type' => 'php', 'content' => $ePhp];
        } else {
            //is array / object
            $entity['json'] = ['type' => 'json', 'content' => json_encode($e)];
            $ePhp = 'return ' . var_export($e, true) . ';';
            $entity['php'] = ['type' => 'php', 'content' => $ePhp];
        }
        return $entity;
    }
    public function saveEntity(string $entityName, $entity)
    {
        $existingEntity = $this->loadEntity($entityName);
        $entityFiles = $this->generateEntityFiles($entity);
        if ($existingEntity) {
            foreach (['json', 'php'] as $f) {
                if ($existingEntity->get('isDirectory')) {
                    $fileName = $existingEntity->get('path') . 'schema.' . $f;
                } else {
                    if (!is_dir($this->directory . 'schemas')) {
                        mkdir($this->directory . 'schemas', 0755);
                    }
                    $fileName = $this->directory . $entityName . '.' . $f;
                    if ($f == 'php') {
                        $fileName = $this->directory . 'schemas/' . $entityName . '-schema.' . $f;
                    }
                }
                file_put_contents($fileName, $entityFiles[$f]['content']);
            }
        } else {
            if (!is_dir($this->directory . '/' . strtolower($entityName))) {
                mkdir($this->directory . '/' . strtolower($entityName), 0755);
            }
            foreach (['json', 'php'] as $f) {
                $fileName = $this->directory . '/' . strtolower($entityName) . '/schema.' . $f;
                file_put_contents($fileName, $entityFiles[$f]['content']);
            }
        }
        return $this->loadEntity($entityName);
    }
}
