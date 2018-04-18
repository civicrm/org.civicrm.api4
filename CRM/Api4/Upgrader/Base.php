<?php

// AUTO-GENERATED FILE -- Civix may overwrite any changes made to this file

/**
 * Base class which provides helpers to execute upgrade logic
 *
 * @property \CRM_Queue_Queue queue
*/
class CRM_Api4_Upgrader_Base
{

  /**
   * @var CRM_Api4_Upgrader, subclass of ttis
   */
    static $instance;

  /**
   * @var CRM_Queue_TaskContext
   */
    protected $ctx;

  /**
   * @var string, eg 'com.example.myextension'
   */
    protected $extensionName;

  /**
   * @var string, full path to the extension's source tree
   */
    protected $extensionDir;

  /**
   * @var array(revisionNumber) sorted numerically
   */
    private $revisions;

  /**
   * @var boolean
   *   Flag to clean up extension revision data in civicrm_setting
   */
    private $revisionStorageIsDeprecated = false;

  /**
   * Obtain a reference to the active upgrade handler.
   */
    public static function instance()
    {
        if (!self::$instance) {
          // FIXME auto-generate
            self::$instance = new CRM_Api4_Upgrader(
                'org.civicrm.api4',
                dirname(__DIR__, 3).'/'
            );
        }
        return self::$instance;
    }

  /**
   * Adapter that lets you add normal (non-static) member functions to the queue.
   *
   * Note: Each upgrader instance should only be associated with one
   * task-context; otherwise, this will be non-reentrant.
   *
   * @code
   * CRM_Api4_Upgrader_Base::_queueAdapter($ctx, 'methodName', 'arg1', 'arg2');
   * @endcode
   */
    public static function _queueAdapter()
    {
        $instance = self::instance();
        $args = func_get_args();
        $instance->ctx = array_shift($args);
        $instance->queue = $instance->ctx->queue;
        $method = array_shift($args);
        return call_user_func_array([$instance, $method], $args);
    }
    
    /**
     * CRM_Api4_Upgrader_Base constructor.
     *
     * @param $extensionName
     * @param $extensionDir
     */
    public function __construct($extensionName, $extensionDir)
    {
        $this->extensionName = $extensionName;
        $this->extensionDir = $extensionDir;
    }

  // ******** Task helpers ********
    
    /**
     * Run a CustomData file.
     *
     * @param string $relativePath the CustomData XML file path (relative to this extension's dir)
     *
     * @return bool
     * @throws \CRM_Core_Exception
     */
    public function executeCustomDataFile($relativePath)
    {
        $xml_file = $this->extensionDir . '/' . $relativePath;
        return static::executeCustomDataFileByAbsPath($xml_file);
    }
    
    /**
     * Run a CustomData file
     *
     * @param string $xml_file the CustomData XML file path (absolute path)
     *
     * @return bool
     * @throws \CRM_Core_Exception
     */
    protected static function executeCustomDataFileByAbsPath($xml_file)
    {
        $import = new CRM_Utils_Migrate_Import();
        $import->run($xml_file);
        return true;
    }
    
    /**
     * Run a SQL file.
     *
     * @param string $relativePath the SQL file path (relative to this extension's dir)
     *
     * @return bool
     * @throws \CRM_Exception
     */
    public function executeSqlFile($relativePath)
    {
        CRM_Utils_File::sourceSQLFile(
            CIVICRM_DSN,
            $this->extensionDir . DIRECTORY_SEPARATOR . $relativePath
        );
        return true;
    }
    
    /**
     * @param string $tplFile
     *   The SQL file path (relative to this extension's dir).
     *   Ex: "sql/mydata.mysql.tpl".
     *
     * @return bool
     * @throws \CRM_Exception
     */
    public function executeSqlTemplate($tplFile)
    {
      // Assign multilingual variable to Smarty.
        $upgrade = new CRM_Upgrade_Form();

        $tplFile = CRM_Utils_File::isAbsolute($tplFile) ? $tplFile : $this->extensionDir . DIRECTORY_SEPARATOR . $tplFile;
        $smarty = CRM_Core_Smarty::singleton();
        $smarty->assign('domainID', CRM_Core_Config::domainID());
        CRM_Utils_File::sourceSQLFile(
            CIVICRM_DSN,
            $smarty->fetch($tplFile),
            null,
            true
        );
        return true;
    }
    
    /**
     * Run one SQL query.
     *
     * This is just a wrapper for CRM_Core_DAO::executeSql, but it
     * provides syntatic sugar for queueing several tasks that
     * run different queries
     *
     * @param       $query
     * @param array $params
     *
     * @return bool
     */
    public function executeSql($query, $params = [])
    {
      // FIXME verify that we raise an exception on error
        CRM_Core_DAO::executeQuery($query, $params);
        return true;
    }
    
    /**
     * Syntatic sugar for enqueuing a task which calls a function in this class.
     *
     * The task is weighted so that it is processed
     * as part of the currently-pending revision.
     *
     * After passing the $funcName, you can also pass parameters that will go to
     * the function. Note that all params must be serializable.
     *
     * @param $title
     *
     */
    public function addTask($title)
    {
        $args = func_get_args();
        $title = array_shift($args);
        $task = new CRM_Queue_Task(
            [get_class($this), '_queueAdapter'],
            $args,
            $title
        );
        return $this->queue->createItem($task, ['weight' => -1]);
    }

  // ******** Revision-tracking helpers ********
    
    /**
     * Determine if there are any pending revisions.
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function hasPendingRevisions()
    {
        $revisions = $this->getRevisions();
        $currentRevision = $this->getCurrentRevision();

        if (empty($revisions)) {
            return false;
        }
        if (empty($currentRevision)) {
            return true;
        }

        return ($currentRevision < max($revisions));
    }
    
    /**
     * Add any pending revisions to the queue.
     *
     * @param \CRM_Queue_Queue $queue
     *
     * @throws \ReflectionException
     */
    public function enqueuePendingRevisions(CRM_Queue_Queue $queue)
    {
        $this->queue = $queue;

        $currentRevision = $this->getCurrentRevision();
        foreach ($this->getRevisions() as $revision) {
            if ($revision > $currentRevision) {
                $title = ts('Upgrade %1 to revision %2', [
                1 => $this->extensionName,
                2 => $revision,
                ]);

              // note: don't use addTask() because it sets weight=-1

                $task = new CRM_Queue_Task(
                    [get_class($this), '_queueAdapter'],
                    ['upgrade_' . $revision],
                    $title
                );
                $this->queue->createItem($task);

                $task = new CRM_Queue_Task(
                    [get_class($this), '_queueAdapter'],
                    ['setCurrentRevision', $revision],
                    $title
                );
                $this->queue->createItem($task);
            }
        }
    }
    
    /**
     * Get a list of revisions.
     *
     * @return array(revisionNumbers) sorted numerically
     * @throws \ReflectionException
     */
    public function getRevisions()
    {
        if (!is_array($this->revisions)) {
            $this->revisions = [];

            $clazz = new ReflectionClass(get_class($this));
            $methods = $clazz->getMethods();
            foreach ($methods as $method) {
                if (preg_match('/^upgrade_(.*)/', $method->name, $matches)) {
                    $this->revisions[] = $matches[1];
                }
            }
            sort($this->revisions, SORT_NUMERIC);
        }

        return $this->revisions;
    }
    
    /**
     * @return mixed|string
     */
    public function getCurrentRevision()
    {
        $revision = CRM_Core_BAO_Extension::getSchemaVersion($this->extensionName);
        if (!$revision) {
            $revision = $this->getCurrentRevisionDeprecated();
        }
        return $revision;
    }
    
    /**
     * @return mixed
     */
    private function getCurrentRevisionDeprecated()
    {
        $key = $this->extensionName . ':version';
        if ($revision = CRM_Core_BAO_Setting::getItem('Extension', $key)) {
            $this->revisionStorageIsDeprecated = true;
        }
        return $revision;
    }
    
    /**
     * @param $revision
     *
     * @return bool
     */
    public function setCurrentRevision($revision)
    {
        CRM_Core_BAO_Extension::setSchemaVersion($this->extensionName, $revision);
    // clean up legacy schema version store (CRM-19252)
        $this->deleteDeprecatedRevision();
        return true;
    }

    private function deleteDeprecatedRevision()
    {
        if ($this->revisionStorageIsDeprecated) {
            $setting = new CRM_Core_BAO_Setting();
            $setting->name = $this->extensionName . ':version';
            $setting->delete();
            CRM_Core_Error::debug_log_message("Migrated extension schema revision ID for {$this->extensionName} from civicrm_setting (deprecated) to civicrm_extension.\n");
        }
    }

  // ******** Hook delegates ********
    
    /**
     * @see https://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
     * @throws \CRM_Exception
     * @throws \CRM_Core_Exception
     */
    public function onInstall()
    {
        $files = glob($this->extensionDir . '/sql/*_install.sql');
        if (is_array($files)) {
            foreach ($files as $file) {
                CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, $file);
            }
        }
        $files = glob($this->extensionDir . '/sql/*_install.mysql.tpl');
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->executeSqlTemplate($file);
            }
        }
        $files = glob($this->extensionDir . '/xml/*_install.xml');
        if (is_array($files)) {
            foreach ($files as $file) {
                static::executeCustomDataFileByAbsPath($file);
            }
        }
        if (is_callable([$this, 'install'])) {
            $this->install();
        }
    }
    
    /**
     * @see https://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
     * @throws \ReflectionException
     */
    public function onPostInstall()
    {
        $revisions = $this->getRevisions();
        if (!empty($revisions)) {
            $this->setCurrentRevision(max($revisions));
        }
        if (is_callable([$this, 'postInstall'])) {
            $this->postInstall();
        }
    }
    
    /**
     * @see https://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
     * @throws \CRM_Exception
     */
    public function onUninstall()
    {
        $files = glob($this->extensionDir . '/sql/*_uninstall.mysql.tpl');
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->executeSqlTemplate($file);
            }
        }
        if (is_callable([$this, 'uninstall'])) {
            $this->uninstall();
        }
        $files = glob($this->extensionDir . '/sql/*_uninstall.sql');
        if (is_array($files)) {
            foreach ($files as $file) {
                CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, $file);
            }
        }
    }

  /**
   * @see https://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
   */
    public function onEnable()
    {
      // stub for possible future use
        if (is_callable([$this, 'enable'])) {
            $this->enable();
        }
    }

  /**
   * @see https://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
   */
    public function onDisable()
    {
      // stub for possible future use
        if (is_callable([$this, 'disable'])) {
            $this->disable();
        }
    }
    
    /**
     * @param                       $op
     * @param \CRM_Queue_Queue|null $queue
     *
     * @return array|void
     */
    public function onUpgrade($op, CRM_Queue_Queue $queue = null)
    {
        switch ($op) {
            case 'check':
                return [$this->hasPendingRevisions()];

            case 'enqueue':
                return $this->enqueuePendingRevisions($queue);

            default:
        }
    }
}
