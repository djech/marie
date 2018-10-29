<?php

namespace App\Controller;

use App\Entity\About;
use App\Entity\Contact;
use App\Entity\Information;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 17/10/18
 * Time: 10:54
 */

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function homeAction()
    {
        $information = $this->getDoctrine()->getRepository(Information::class)->findOrCreateOne();

        if($information->getAbout() == null)
        {
            $about = new About();
            $information->setAbout($about);
            $em = $this->getDoctrine()->getManager();
            $em->persist($about);
            $information->setAbout($about);
            $em->persist($information);
            $em->flush();
        }

        $contact = new Contact();

        # Add form fields
        $form = $this->createFormBuilder($contact)
            ->add('name', TextType::class, array('label'=> true, 'attr' => array('class' => 'input-field', 'placeholder' => 'Votre nom')))
            ->add('email', TextType::class, array('label'=> 'Email','attr' => array('class' => 'input-field', 'placeholder' => 'Email')))
            ->add('message', TextareaType::class, array('label'=> 'Message','attr' => array('class' => 'input-field', 'placeholder' => 'Message')))
            ->getForm();

        if($form->isSubmitted() &&  $form->isValid()) {

            $name = $form['name']->getData();
            $email = $form['email']->getData();
            $message = $form['message']->getData();

            # set form data

            $contact->setName($name);
            $contact->setEmail($email);
            $contact->setMessage($message);

            # finally add data in database

            $sn = $this->getDoctrine()->getManager();
            $sn->persist($contact);
            $sn->flush();

            $mail = \Swift_Message::newInstance()
                ->setSubject("Formulaire contact marie-bieber.fr")
                ->setFrom('contact@marie-bieber.fr','Contact')
                ->setTo($information->getEmail())
                ->setBody($this->renderView('administration/sendemail.html.twig',array('name' => $name, 'email' => $email, 'message' => $message)),'text/html');

            $this->get('mailer')->send($mail);

        }

        return $this->render('index.html.twig', array(
            'information' => $information,
            'form' => $form->createView()
        ));
    }

}