protected function setUp()
{
    parent::setUp();

    $this->factory = Forms::createFormFactoryBuilder()
        ->addExtensions($this->getExtensions())
        ->getFormFactory();
}

protected function getExtensions()
{
    $mockEntityType = $this->getMockBuilder('Symfony\Bridge\Doctrine\Form\Type\EntityType')
        ->disableOriginalConstructor()
        ->getMock();

    $mockEntityType->expects($this->any())->method('getName')
        ->will($this->returnValue('entity'));

    return array(new PreloadedExtension(array(
        $mockEntityType->getName() => $mockEntityType,
    ), array()));
}

public function testSubmitValidData()
{
    $formData = array(
        'name' => 'Mbalmayo',
        'latitude' => 3.5165475,
        'longitude' => 11.5144015,
        'zoomLevel' => 12.0,
        'region' => 'Centre',
    );

    $type = new CitiesType();
    $form = $this->factory->create($type, null);

    $object = new Cities();
    $object->fromArray($formData);

    // submit the data to the form directly
    $form->submit($formData);

    $this->assertTrue($form->isSynchronized());
    $this->assertEquals($object, $form->getData());

    $view = $form->createView();
    $children = $view->children;

    foreach (array_keys($formData) as $key) {
        $this->assertArrayHasKey($key, $children);
    }
}
