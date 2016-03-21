<?php

namespace PathologyBundle\Tests\Form;

use PathologyBundle\Form\UserPatientType;
use Symfony\Component\Form\Test\TypeTestCase;
use Faker;

class UserPatientTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $faker = Faker\Factory::create();
        $formData = array(
            'username' => $faker->userName,
            'fname' => $faker->firstName,
            'lname' => $faker->lastName,
            'password' => $faker->word(10),
            'email' => $faker->email,
            'phoneNumber' => $faker->phoneNumber,
        );

        // Verify if the FormType compiles.
        $form = $this->factory->create(UserPatientType::class);

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
