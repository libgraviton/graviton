<?php
namespace Graviton\CoreBundle\Document;


interface GravitonShowcaseInterface
{
    public function getId();
    public function getAnotherint();
    public function setAnotherint($int);
    public function getTestField();
    public function setTestField($field);
    public function getEmail();
    public function setEmail($email);
    public function getSomeotherfield();
    public function setSomeotherfield($field);
    public function isAboolean();
    public function setAboolean($bool);
    public function isOptionalboolean();
    public function setOptionalboolean($bool);
    public function getSomefloatydouble();
    public function setSomefloatydouble($float);
    public function getModificationdate();
    public function setModificationdate($date);
    public function getContact();
    public function setContact(\Graviton\PersonBundle\Document\PersonContact $contact);
    public function getContacts();
    public function setContacts($contacts);
    public function getContactcode();
    public function setContactcode(\GravitonDyn\ShowcaseBundle\Document\ShowcaseContactCode $contactCode);
    public function getNestedarray();
    public function setNestedarray($array);
    public function getUnstructedobject();
    public function setUnstructedobject($object);
}
