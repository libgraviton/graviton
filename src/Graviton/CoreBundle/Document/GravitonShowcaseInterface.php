<?php
namespace Graviton\CoreBundle\Document;


interface GravitonShowcaseInterface
{
    public function getId();
    public function getAnotherInt();
    public function getTestField();
    public function getEmail();
    public function getSomeOtherField();
    public function getABoolean();
    public function getOptionalBoolean();
    public function getSomeFloatDouble();
    public function getModificationDate();
    public function getContact();
    public function getContacts();
    public function getContactCode();
    public function getNestedArray();
}
