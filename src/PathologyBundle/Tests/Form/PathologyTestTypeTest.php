<?php

namespace PathologyBundle\Tests\Form;

use PathologyBundle\Form\PathologyTestType;
use Symfony\Component\Form\Test\TypeTestCase;
use Faker;

class PathologyTestTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $faker = Faker\Factory::create();
        $formData = array(
            'name' => $faker->word(10),
            'referenceValue' => $faker->randomDigit,
            'unit' => $faker->text,
        );

        // Verify if the FormType compiles.
        $form = $this->factory->create(PathologyTestType::class);

        // Verify none of your data transformers used by the form failed
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        // Verify all widgets are available in the children property:
        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
