<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

use Citrus\DHFi\Util\CurrencyInstaller;

Loc::loadMessages(__FILE__);
IncludeModuleLangFile(__FILE__); // for Marketplace compatibility

class citrus_dhfi extends CModule
{
	const PHP_MIN_VERSION = '7.4.0';
	const MAIN_MIN_VERSION = '21.400.0';
	const CRM_MIN_VERSION = '21.700.0';

	/**
	 * @var string Код модуля
	 */
	public $MODULE_ID = "citrus.dhfi";
	/**
	 * @var string Права модуля
	 */
	public $MODULE_GROUP_RIGHTS = 'N';
	/**
	 * @var string Версия модуля
	 */
	public $MODULE_VERSION = '0.0.1';
	/**
	 * @var string Дата создания или обновления модуля в формате Y-m-d
	 */
	public $MODULE_VERSION_DATE;
	/**
	 * @var string Название модуля
	 */
	public $MODULE_NAME;
	/**
	 * @var string Описание модуля
	 */
	public $MODULE_DESCRIPTION;
	/**
	 * @var string Автор модуля
	 */
	public $PARTNER_NAME;
	/**
	 * @var string Ссылка на сайт автора
	 */
	public $PARTNER_URI;

	/**
	 * citrus_module_noname constructor.
	 */
	public function __construct()
	{
		// register autoloading for module classes and define module constants
		require_once dirname(__DIR__) . "/include.php";

		$this->loadVersion();

		$this->MODULE_NAME = GetMessage('CITRUS_DHFI_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('CITRUS_DHFI_MODULE_DESCRIPTION');
		// если заполнять PARTNER_NAME и PARTNER_URI через Loc::getMessage, файл не проходит проверку на маркетплейсе
		$this->PARTNER_NAME = GetMessage("CITRUS_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("CITRUS_PARTNER_URI");
	}

	/**
	 * Установка модуля
	 */
	public function DoInstall()
	{
		global $APPLICATION;

		$APPLICATION->ResetException();

		if (!check_bitrix_sessid()) {
			return false;
		}

		return $this->InstallDB() && $this->InstallEvents() && $this->InstallFiles();
	}

	/**
	 * Удаление моудля и всех его составляющих
	 */
	function DoUninstall()
	{
		global $APPLICATION;

		$APPLICATION->ResetException();

		if (!check_bitrix_sessid()) {
			return false;
		}

		return $this->UnInstallFiles() && $this->UnInstallEvents() && $this->UnInstallDB();
	}

	/**
	 * Загрузка данных в базы или создание необходимых таблиц
	 *
	 * @return bool
	 */
	function InstallDB()
	{
		global $APPLICATION;

		$connection = Main\Application::getConnection();
		$connection->startTransaction();
		try {
			if (version_compare(PHP_VERSION, self::PHP_MIN_VERSION) < 0) {
				throw new Exception(
					GetMessage("CITRUS_INSTALL_ERROR_REQUIRED_PHP_VERSION", [
						"#GOT#" => PHP_VERSION,
						"#NEED#" => self::PHP_MIN_VERSION
					])
				);
			}
			if (version_compare(SM_VERSION, self::MAIN_MIN_VERSION, "<")) {
				throw new Exception(
					GetMessage("CITRUS_INSTALL_ERROR_REQUIRED_VERSION", [
						"#GOT#" => SM_VERSION,
						"#NEED#" => self::MAIN_MIN_VERSION
					])
				);
			}

			if (!extension_loaded('curl')) {
				throw new Exception(Loc::getMessage('CITRUS_INSTALL_ERROR_REQUIRED_CURL'));
			}

			if (!Main\ModuleManager::isModuleInstalled('crm')) {
				throw new Exception(Loc::getMessage('CITRUS_INSTALL_ERROR_MISSING_MODULE', ['#MODULE#' => "CRM"]));
			}
			if (version_compare(Main\ModuleManager::getVersion('crm'), self::CRM_MIN_VERSION, "<")) {
				throw new Exception(
					GetMessage("CITRUS_INSTALL_ERROR_CRM_VERSION_MISMATCH", [
						"#GOT#" => Main\ModuleManager::getVersion('crm'),
						"#NEED#" => self::CRM_MIN_VERSION
					])
				);
			}

			$errors = $connection->executeSqlBatch(file_get_contents(__DIR__ . '/db/install.sql'),
				true);
			if (count($errors)) {
				throw new Exception(implode(PHP_EOL, $errors));
			}

			Main\ModuleManager::registerModule($this->MODULE_ID);

			$this->installEventHandlers();
			$this->installCurrency();

			Main\Application::getInstance()->getTaggedCache()->clearByTag('intranet_menu_binding');

			$connection->commitTransaction();

			return true;
		} catch (Exception $e) {
			$connection->rollbackTransaction();
			$APPLICATION->ThrowException($e->getMessage());

			Main\ModuleManager::unRegisterModule($this->MODULE_ID);
			return false;
		}
	}

	/**
	 * Удаление данных из базы
	 * @return bool
	 */
	function UnInstallDB()
	{
		Main\Loader::registerNamespace('\Citrus\DHFi', dirname(__DIR__) . '/lib');

		$connection = Main\Application::getConnection();
		$connection->startTransaction();
		try {
			$tableName = \Citrus\DHFi\Entity\PaymentTable::getTableName();
			if (Main\Application::getConnection()->isTableExists($tableName)) {
				Main\Application::getConnection()->dropTable($tableName);
			}

			$this->uninstallEventHandlers();
			Main\Application::getInstance()->getTaggedCache()->clearByTag('intranet_menu_binding');

			Main\Config\Option::delete($this->MODULE_ID);

			// with agents and rights
			Main\ModuleManager::unRegisterModule($this->MODULE_ID);

			$connection->commitTransaction();
		} catch (Throwable $exception) {
			$connection->rollbackTransaction();
		}

		return true;
	}

	/**
	 * Регистрация типов и почтовых событий
	 *
	 * @return bool
	 */
	public function InstallEvents()
	{
		return true;
	}

	/**
	 * Отмена регистрации типов и почтовых событий
	 *
	 * @return bool
	 */
	public function UninstallEvents()
	{
		return true;
	}

	protected function installEventHandlers()
	{
		$eventManager = Main\EventManager::getInstance();
	}

	protected function uninstallEventHandlers()
	{
		$eventManager = Main\EventManager::getInstance();
	}

	/**
	 * Копирование файлов модуля
	 *
	 * @return bool
	 */
	public function InstallFiles()
	{
		CopyDirFiles(__DIR__ . "/php_interface", $_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/php_interface", true, true);
		CopyDirFiles(__DIR__ . "/images", $_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/images", true, true);

		return true;
	}

	/**
	 * Удаление файлов модуля
	 *
	 * @return bool
	 */
	public function UninstallFiles()
	{
		$this->removeInstalled(__DIR__ . "/php_interface", $_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/php_interface");
		$this->removeInstalled(__DIR__ . "/images", $_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/images");

		return true;
	}

	/**
	 * @return string Путь установки модуля от корня сайта: папка /bitrix или /local
	 */
	protected function getBxRoot()
	{
		return strpos(getLocalPath('modules/' . $this->MODULE_ID), '/local') === 0 ? '/local' : BX_ROOT;
	}

	/**
	 * Загружает версию модуля из version.php
	 */
	protected function loadVersion()
	{
		$arModuleVersion = array(
			"VERSION" => "0.0.1",
			"VERSION_DATE" => DateTime::createFromFormat('Y-m-d', time()),
		);

		@include __DIR__ . '/version.php';

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}
	}

	/**
	 * Удаляет файлы модуля, скопированные в $target.
	 *
	 * Действие обратное функции {@link https://dev.1c-bitrix.ru/api_help/main/functions/file/copydirfiles.php CopyDirFiles}.
	 *
	 * Удаляет только файлы и папки, которые есть в $source по аналогии с {@link https://dev.1c-bitrix.ru/api_help/main/functions/file/deletedirfiles.php DeleteDirFiles}.
	 *
	 * В отличие от {@link https://dev.1c-bitrix.ru/api_help/main/functions/file/deletedirfiles.php DeleteDirFiles} поддерживает рекурсию и обрабатывает каталоги наравне с файлами.
	 *
	 * @param string $source Абсолютный путь к папке, откуда скопированы файлы
	 * @param string $target Абсолютный путь к папке, куда скопированы файлы
	 * @return bool
	 */
	public function removeInstalled($source, $target)
	{
		$target = Main\IO\Path::normalize($target);
		$source = Main\IO\Path::normalize($source);

		if (!file_exists($target) || !is_dir($target)) {
			return false;
		}

		if (!file_exists($source) || !is_dir($source)) {
			return false;
		}

		if (!is_readable($target)) {
			throw new RuntimeException('target is not readable');
		}

		if (!is_readable($source)) {
			throw new RuntimeException('source is not readable');
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		/** @var SplFileInfo $fileInfo */
		foreach ($iterator as $fileInfo) {
			/** @var RecursiveDirectoryIterator $subIterator */
			$subIterator = $iterator->getSubIterator();

			$targetPath = Main\IO\Path::normalize($target . '/' . $subIterator->getSubPathname());

			// used in development
			if (is_link($targetPath)) {
				continue;
			}

			if (is_file($targetPath) || is_dir($targetPath)) {
				$fn = is_dir($targetPath) ? 'rmdir' : 'unlink';
				$fn($targetPath);
			}
		}

		if (count(scandir($target, SORT_REGULAR)) == 2) // empty directory
		{
			@rmdir($target);
		}

		return true;
	}

	protected function installCurrency(): void
	{
		Main\Loader::registerNamespace('\Citrus\DHFi', dirname(__DIR__) . '/lib');
		CurrencyInstaller::installOrUpdate();
	}
}
