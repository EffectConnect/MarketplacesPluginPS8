<?php

namespace EffectConnect\Marketplaces\Controller;

use EffectConnect\Marketplaces\Exception\FileZipCreationFailedException;
use EffectConnect\Marketplaces\Helper\FileCleanHelper;
use EffectConnect\Marketplaces\Helper\FileDownloadHelper;
use EffectConnect\Marketplaces\Helper\FilePathInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminLogController
 * @package EffectConnect\Marketplaces\Controller
 */
class AdminLogController extends CompatibleAdminController
{
    /**
     * @param Request $request
     * @return Response|null
     */
    public function indexAction(Request $request)
    {
        return $this->render('@Modules/effectconnect_marketplaces/views/templates/admin/LogController.index.html.twig', [
            'layoutTitle'        => $this->translate('Logs'),
            'enableSidebar'      => true,
            'dataFolder'         => realpath(FilePathInterface::DATA_DIRECTORY),
            'fileExpirationDays' => FileCleanHelper::TMP_FILE_EXPIRATION_DAYS,
        ]);
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse|RedirectResponse
     */
    public function downloadAction(Request $request)
    {
        try {
            $zipFileName = FileDownloadHelper::downloadDataFolderZip();
        } catch (FileZipCreationFailedException $e) {
            $this->addFlash('error', $this->translate($e->getMessage()));
            return $this->redirectToRoute('effectconnect_marketplaces_admin_log_index');
        }

        $file = new File($zipFileName);
        return $this->file($file);
    }
}
