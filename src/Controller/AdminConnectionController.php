<?php

namespace EffectConnect\Marketplaces\Controller;

use Configuration;
use EffectConnect\Marketplaces\Enums\ConfigurationKeys;
use EffectConnect\Marketplaces\Form\Type\ChoiceProvider\CarrierChoiceProvider;
use EffectConnect\Marketplaces\Form\Type\ChoiceProvider\PaymentModuleChoiceProvider;
use EffectConnect\Marketplaces\Grid\AdminConnectionGridDefinitionFactory;
use EffectConnect\Marketplaces\Filter\AdminConnectionFilter;
use EffectConnect\Marketplaces\LegacyWrappers\LegacyShopContext;
use EffectConnect\Marketplaces\Model\Connection;
use Exception;
use PrestaShop\PrestaShop\Adapter\Module\ModuleDataProvider;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;
use PrestaShop\PrestaShop\Core\Grid\Filter\GridFilterFormFactory;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShop\PrestaShop\Core\Grid\Presenter\GridPresenter;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Builder\FormBuilder;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Handler\FormHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Validate;

/**
 * Class AdminConnectionController
 * @package EffectConnect\Marketplaces\Controller
 */
class AdminConnectionController extends FrameworkBundleAdminController
{
    /**
     * @var string
     */
    protected $paymentModulePath = __DIR__ . '/../../../effectconnect_marketplacespayment';

    /**
     * @var string
     */
    protected $paymentModuleName = 'effectconnect_marketplacespayment';

    /**
     * @var FormBuilder
     */
    protected $_formBuilder;

    /**
     * @var FormHandler
     */
    protected $_formHandler;

    /**
     * @var AdminConnectionGridDefinitionFactory
     */
    protected $_gridDefinitionFactory;

    /**
     * @var GridFactory
     */
    protected $_gridFactory;

    /**
     * @var GridFilterFormFactory
     */
    protected $_gridFilterFormFactory;

    /**
     * @var GridPresenter
     */
    protected $_gridPresenter;

    /**
     * @var LegacyShopContext
     */
    protected $_legacyShopContext;

    /**
     * @var CarrierChoiceProvider
     */
    protected $_carrierChoiceProvider;

    /**
     * @var PaymentModuleChoiceProvider
     */
    protected $_paymentModuleChoiceProvider;

    /**
     * @var ModuleManager
     */
    protected $_moduleManager;

    /**
     * @var ModuleDataProvider
     */
    protected $_moduleDataProvider;

    /**
     * AdminConnectionController constructor.
     *
     * @param FormBuilder $formBuilder
     * @param FormHandler $formHandler
     * @param AdminConnectionGridDefinitionFactory $gridDefinitionFactory
     * @param GridFactory $gridFactory
     * @param GridFilterFormFactory $gridFilterFormFactory
     * @param GridPresenter $gridPresenter
     * @param LegacyShopContext $legacyShopContext
     * @param ModuleManager $moduleManager
     * @param ModuleDataProvider $moduleDataProvider
     */
    public function __construct(
        FormBuilder $formBuilder,
        FormHandler $formHandler,
        AdminConnectionGridDefinitionFactory $gridDefinitionFactory,
        GridFactory $gridFactory,
        GridFilterFormFactory $gridFilterFormFactory,
        GridPresenter $gridPresenter,
        LegacyShopContext $legacyShopContext,
        ModuleManager $moduleManager,
        ModuleDataProvider $moduleDataProvider
    ) {
        $this->_formBuilder                 = $formBuilder;
        $this->_formHandler                 = $formHandler;
        $this->_gridDefinitionFactory       = $gridDefinitionFactory;
        $this->_gridFactory                 = $gridFactory;
        $this->_gridFilterFormFactory       = $gridFilterFormFactory;
        $this->_gridPresenter               = $gridPresenter;
        $this->_legacyShopContext           = $legacyShopContext;
        $this->_moduleManager               = $moduleManager;
        $this->_moduleDataProvider          = $moduleDataProvider;
        parent::__construct();
    }

    /**
     * @param Request $request
     * @param AdminConnectionFilter $filters
     * @return Response|null
     */
    public function indexAction(Request $request, AdminConnectionFilter $filters)
    {
        $grid = $this->_gridFactory->getGrid($filters);

        return $this->render('@Modules/effectconnect_marketplaces/views/templates/admin/AdminConnectionController.index.html.twig', [
            'layoutTitle'         => $this->trans('Connections', 'Modules.Effectconnectmarketplaces.Admin'),
            'AdminConnectionGrid' => $this->_gridPresenter->present($grid),
            'enableSidebar'       => true,
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function searchAction(Request $request)
    {
        $groupDefinition = $this->_gridDefinitionFactory->getDefinition();
        $filtersForm     = $this->_gridFilterFormFactory->create($groupDefinition);
        $filtersForm->handleRequest($request);

        $filters = [];
        if ($filtersForm->isSubmitted()) {
            $filters = $filtersForm->getData();
        }
        return $this->redirectToRoute('effectconnect_marketplaces_adminconnection_index', ['filters' => $filters]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function addAction(Request $request)
    {
        $form = $this->_formBuilder->getForm();
        $form->handleRequest($request);

        try {
            $result = $this->_formHandler->handle($form);
            if ($result->isSubmitted() && $result->isValid()) {
                $this->addFlash('success', $this->trans('Connection successful created.', 'Modules.Effectconnectmarketplaces.Admin'));
                return $this->redirectToRoute('effectconnect_marketplaces_adminconnection_index');
          }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('effectconnect_marketplaces_adminconnection_index');
        }

        return $this->render('@Modules/effectconnect_marketplaces/views/templates/admin/AdminConnectionController.form.html.twig', [
            'layoutTitle'             => $this->trans('Add connection', 'Modules.Effectconnectmarketplaces.Admin'),
            'requireAddonsSearch'     => false,
            'enableSidebar'           => true,
            'AdminConnectionForm'     => $form->createView(),
            'isAllOrOnlyShopContext'  => $this->_legacyShopContext->isAllOrOnlyShopContext(),
            'paymentModuleHasSymlink' => $this->paymentModuleHasSymlink(),
            'paymentModuleIsActive'   => $this->paymentModuleIsActive(),
            'paymentModuleExists'     => $this->paymentModuleExists()
        ]);
    }

    /**
     * @param int $recordId
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function editAction(int $recordId, Request $request)
    {
        $form = $this->_formBuilder->getFormFor($recordId, $request->request->get('admin_connection_form') ?? []);
        $form->handleRequest($request);

        try {
            $result = $this->_formHandler->handleFor($recordId, $form);
            if ($result->isSubmitted() && $result->isValid()) {
                $this->addFlash('success', $this->trans('Connection successful updated.', 'Modules.Effectconnectmarketplaces.Admin'));
                return $this->redirectToRoute('effectconnect_marketplaces_adminconnection_index');
          }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('effectconnect_marketplaces_adminconnection_index');
        }

        return $this->render('@Modules/effectconnect_marketplaces/views/templates/admin/AdminConnectionController.form.html.twig', [
            'layoutTitle'             => $this->trans('Edit connection', 'Modules.Effectconnectmarketplaces.Admin'),
            'requireAddonsSearch'     => false,
            'enableSidebar'           => true,
            'AdminConnectionForm'     => $form->createView(),
            'isAllOrOnlyShopContext'  => $this->_legacyShopContext->isAllOrOnlyShopContext(),
            'paymentModuleHasSymlink' => $this->paymentModuleHasSymlink(),
            'paymentModuleIsActive'   => $this->paymentModuleIsActive(),
            'paymentModuleExists'     => $this->paymentModuleExists()
        ]);
    }

    /**
     * @param int $recordId
     * @return RedirectResponse
     */
    public function toggleActiveAction(int $recordId)
    {
        try {
            $record = new Connection($recordId);
            if (Validate::isLoadedObject($record)) {
                $record->is_active = !intval($record->is_active);
                $record->save();
                $this->addFlash(
                    'success',
                    $this->trans('The connection has been successfully updated.', 'Modules.Effectconnectmarketplaces.Admin')
                );
            }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('effectconnect_marketplaces_adminconnection_index');
    }

    /**
     * @param int $recordId
     * @return RedirectResponse
     */
    public function deleteAction(int $recordId)
    {
        try {
            $record = new Connection($recordId);
            if (Validate::isLoadedObject($record)) {
                if ($record->delete()) {
                    $this->addFlash(
                        'success',
                        $this->trans('Connection successfully deleted', 'Modules.Effectconnectmarketplaces.Admin')
                    );
                } else {
                    $this->addFlash(
                        'error',
                        $this->trans('Connection delete failed', 'Modules.Effectconnectmarketplaces.Admin')
                    );
                }
            } else {
                $this->addFlash(
                    'error',
                    $this->trans('Internal error: recordId is invalid', 'Modules.Effectconnectmarketplaces.Admin')
                );
            }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('effectconnect_marketplaces_adminconnection_index');
    }

    /**
     * @return bool
     */
    protected function paymentModuleHasSymlink()
    {
        return is_link($this->paymentModulePath);
    }

    /**
     * @return bool
     */
    protected function paymentModuleExists()
    {
        return file_exists($this->paymentModulePath);
    }

    /**
     * @return bool
     */
    protected function paymentModuleIsActive()
    {
        return $this->_moduleManager->isInstalled($this->paymentModuleName) && $this->_moduleManager->isEnabled($this->paymentModuleName);
    }

    /**
     * TODO:
     *   - by default all customer groups and all carriers are assigned to a new installed payment module. Is this ok?
     *   - move this logic to PaymentModuleManager?
     *   - activate when installing main module instead of a user action?
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function installPaymentModuleAction(Request $request)
    {
        // Do we have to create a symlink for the module?
        if (!$this->paymentModuleHasSymlink()) {
            try {
                $result = symlink('effectconnect_marketplaces/payment', $this->paymentModulePath);
                if ($result !== true) {
                    $this->addFlash('error', $this->trans('The payment module could not be symlinked.', 'Modules.Effectconnectmarketplaces.Admin'));
                    return $this->redirect($request->headers->get('referer'));
                }
            } catch (Exception $e) {
                $this->addFlash('error', $this->trans('The payment module could not be symlinked (message: %s).', 'Modules.Effectconnectmarketplaces.Admin', [$e->getMessage()]));
                return $this->redirect($request->headers->get('referer'));
            }
        }

        // Is the module correctly symlinked?
        if (!$this->paymentModuleExists()) {
            $this->addFlash('error', $this->trans('The payment module is not correctly symlinked.', 'Modules.Effectconnectmarketplaces.Admin', [$e->getMessage()]));
            return $this->redirect($request->headers->get('referer'));
        }

        // Is the module already installed and enabled?
        if ($this->paymentModuleIsActive()) {
            $this->addFlash('success', $this->trans('The payment module was already installed and can be used now.', 'Modules.Effectconnectmarketplaces.Admin', [$e->getMessage()]));
            return $this->redirect($request->headers->get('referer'));
        }

        // Do we have to enable the module?
        if ($this->_moduleManager->isInstalled($this->paymentModuleName) && !$this->_moduleManager->isEnabled($this->paymentModuleName)) {
            try {
                $result = $this->_moduleManager->enable($this->paymentModuleName);
                if ($result !== true) {
                    $this->addFlash('error', $this->trans('The payment module could not be enabled.', 'Modules.Effectconnectmarketplaces.Admin'));
                    return $this->redirect($request->headers->get('referer'));
                } else {
                    $this->addFlash('success', $this->trans('The payment module was successfully enabled and can be used now.', 'Modules.Effectconnectmarketplaces.Admin'));
                    return $this->redirect($request->headers->get('referer'));
                }
            } catch (Exception $e) {
                $this->addFlash('error', $this->trans('The payment module could not be enabled (message: %s).', 'Modules.Effectconnectmarketplaces.Admin', [$e->getMessage()]));
                return $this->redirect($request->headers->get('referer'));
            }
        }

        // Do we have to install the module?
        if (!$this->_moduleManager->isInstalled($this->paymentModuleName)) {
            try {
                $result = $this->_moduleManager->install($this->paymentModuleName);
                if ($result !== true) {
                    $this->addFlash('error', $this->trans('The payment module could not be installed.', 'Modules.Effectconnectmarketplaces.Admin'));
                    return $this->redirect($request->headers->get('referer'));
                } else {
                    // Save our module ID to the configuration
                    $ecPaymentModuleId = $this->_moduleDataProvider->getModuleIdByName($this->paymentModuleName);
                    Configuration::updateGlobalValue(ConfigurationKeys::EC_DEFAULT_PAYMENT_MODULE_ID, $ecPaymentModuleId);

                    $this->addFlash('success', $this->trans('The payment module was successfully installed and can be used now.', 'Modules.Effectconnectmarketplaces.Admin'));
                    return $this->redirect($request->headers->get('referer'));
                }
            } catch (Exception $e) {
                $this->addFlash('error', $this->trans('The payment module could not be installed (message: %s).', 'Modules.Effectconnectmarketplaces.Admin', [$e->getMessage()]));
                return $this->redirect($request->headers->get('referer'));
            }
        }

        // TODO: test compatibility
        //return $this->redirectToRoute('effectconnect_marketplaces_adminconnection_index');
        return $this->redirect($request->headers->get('referer'));
    }
}
