<?php
/**
 * base form validator
 */

namespace Graviton\RestBundle\Validator;

use Graviton\DocumentBundle\Service\FormDataMapperInterface;
use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;
use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\ExceptionBundle\Exception\NoInputException;
use Graviton\ExceptionBundle\Exception\ValidationException;
use Graviton\RestBundle\Model\DocumentModel;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Graviton\DocumentBundle\Form\Type\DocumentType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Form
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var DocumentType
     */
    private $formType;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param FormFactory        $formFactory Factory, providing different file document instances.
     * @param DocumentType       $formType    Type of form to be set
     * @param ValidatorInterface $validator   Validator to verify correctness of the provided data
     */
    public function __construct(
        FormFactory $formFactory,
        DocumentType $formType,
        ValidatorInterface $validator
    ) {
        $this->formFactory = $formFactory;
        $this->formType = $formType;
        $this->validator = $validator;
    }

    /**
     * @param Request       $request request
     * @param DocumentModel $model   model
     *
     * @return \Symfony\Component\Form\Form
     */
    public function getForm(Request $request, DocumentModel $model)
    {
        $this->formType->initialize($model->getEntityClass());
        return $this->formFactory->create($this->formType, null, ['method' => $request->getMethod()]);
    }

    /**
     * Validates the provided information against a form.
     *
     * @param FormInterface           $form           form to check
     * @param DocumentModel           $model          Model to determine entity to be used
     * @param FormDataMapperInterface $formDataMapper Mapps the entity to form fields
     * @param string                  $jsonContent    json data
     *
     * @throws ValidationException
     * @return mixed
     */
    public function checkForm(
        FormInterface $form,
        DocumentModel $model,
        FormDataMapperInterface $formDataMapper,
        $jsonContent
    ) {
        $document = $formDataMapper->convertToFormData(
            $jsonContent,
            $model->getEntityClass()
        );
        $form->submit($document, true);

        if (!$form->isValid()) {
            throw new ValidationException($form->getErrors(true));
        } else {
            $record = $form->getData();
        }

        return $record;
    }

    /**
     * validate raw json input
     *
     * @param Request  $request  request
     * @param Response $response response
     * @param string   $content  Alternative request content.
     *
     * @return void
     */
    public function checkJsonRequest(Request $request, Response $response, $content = '')
    {
        if (empty($content)) {
            $content = $request->getContent();
        }

        if (is_resource($content)) {
            throw new BadRequestHttpException('unexpected resource in validation');
        }

        // is request body empty
        if ($content === '') {
            $e = new NoInputException();
            $e->setResponse($response);
            throw $e;
        }

        $input = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $e = new MalformedInputException($this->getLastJsonErrorMessage());
            $e->setErrorType(json_last_error());
            $e->setResponse($response);
            throw $e;
        }
        if (!is_array($input)) {
            $e = new MalformedInputException('JSON request body must be an object');
            $e->setResponse($response);
            throw $e;
        }

        if ($request->getMethod() == 'PUT' && array_key_exists('id', $input)) {
            // we need to check for id mismatches....
            if ($request->attributes->get('id') != $input['id']) {
                $e = new MalformedInputException('Record ID in your payload must be the same');
                $e->setResponse($response);
                throw $e;
            }
        }
    }

    /**
     * Validate JSON patch for any object
     *
     * @param array $jsonPatch json patch as array
     *
     * @throws InvalidJsonPatchException
     * @return void
     */
    public function checkJsonPatchRequest(array $jsonPatch)
    {
        foreach ($jsonPatch as $operation) {
            if (!is_array($operation)) {
                throw new InvalidJsonPatchException('Patch request should be an array of operations.');
            }
            if (array_key_exists('path', $operation) && trim($operation['path']) == '/id') {
                throw new InvalidJsonPatchException('Change/remove of ID not allowed');
            }
        }
    }
    /**
     * Used for backwards compatibility to PHP 5.4
     *
     * @return string
     */
    private function getLastJsonErrorMessage()
    {
        $message = 'Unable to decode JSON string';

        if (function_exists('json_last_error_msg')) {
            $message = json_last_error_msg();
        }

        return $message;
    }
}
