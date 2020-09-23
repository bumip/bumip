<?php
namespace Bumip\Core;

/**
 * EntityManager
 * lists all entities,
 * if you load an Entity it will be saved in Data.
 * It does filesystem manipulation, so it can be slow but it should be used only by admins/dev and not so often.
 */
class EntityManager extends DataHolder
{
    private $config;
    private $entities;
    private $directory;
    private $currentEntityName = null;
    private $currentEntityPath = null;

    public function __construct(DataHolder $conf)
    {
        $this->config = $conf;
        $this->directory = empty($this->config->get("entitiesDirectory")) ? 'app/entities/' : $this->config->get("entitiesDirectory");
    }
    public function setDirectory($dir)
    {
        $this->directory = $dir;
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
            } elseif (!is_dir($basePath . $f)) {
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
                $entities[$name]['files'] = $this->getEntityFiles($f);
            }
        }
        $this->entities = $entities;
        return ($entities);
    }
    private function getEntityFiles($path, $isFullPath = false)
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
            $entity = ['name' => \ucfirst($entityName), 'path' => $hasDir[1]];
            //is a directory entity.
            $entity['files'] = $this->getEntityFiles($hasDir[1], true);
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
                    $entity = ['name' => $name, "extention" => $info['extension'], 'path' => $basePath . $info['basename']];
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
    public function saveEntity()
    {
        return false;
    }
}
