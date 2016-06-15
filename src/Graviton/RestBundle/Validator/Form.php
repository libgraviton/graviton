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
}
