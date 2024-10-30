<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToCreateFormCacheException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToDeleteFormCacheException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetrieveFormCacheException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToUpdateFormCacheException;

interface FormCacheService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Retrieves form identified by cache id.
     *
     * @param int $formId
     *
     * @return Form
     *
     * @throws FailedToRetrieveFormCacheException
     */
    public function getForm($formId);

    /**
     * Retrieves form identified by the API id.
     *
     * @param string $apiId
     *
     * @return Form
     *
     * @throws FailedToRetrieveFormCacheException
     */
    public function getFormByApiId($apiId);

    /**
     * Retrieves all cached forms.
     *
     * @return Form[]
     *
     * @throws FailedToRetrieveFormCacheException
     */
    public function getForms();

    /**
     * Creates form in cache.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form $form
     *
     * @return Form
     *
     * @throws FailedToCreateFormCacheException
     */
    public function createForm(Form $form);

    /**
     * Updates form in cache.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form $form
     *
     * @return Form
     *
     * @throws FailedToUpdateFormCacheException
     */
    public function updateForm(Form $form);

    /**
     * Deletes form in cache.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form $form
     *
     * @return void
     *
     * @throws FailedToDeleteFormCacheException
     */
    public function deleteForm(Form $form);
}
