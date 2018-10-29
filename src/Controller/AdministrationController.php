<?php
/**
 * Created by PhpStorm.
 * User: jerome
 * Date: 18/10/18
 * Time: 16:22
 */

namespace App\Controller;

use App\Entity\About;
use App\Entity\Information;
use App\Form\AboutType;
use App\Form\InformationLogicielsType;
use App\Form\InformationSkillsType;
use App\Form\InformationType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdministrationController extends Controller
{
    /**
     * @Route("/admin", name="information")
     */
    public function administrationAction(Request $request)
    {
        $information = $this->getDoctrine()->getRepository(Information::class)->findOrCreateOne();

        $form = $this->get('form.factory')
            ->createBuilder(InformationType::class, $information)
            ->getForm()
        ;

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($information);
                $em->flush();

                $this->addFlash('success', "Information bien modifié !");

            }
        }

        return $this->render('administration/information.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/admin/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        $information = $this->getDoctrine()->getRepository(Information::class)->findOrCreateOne();

        if($information->getAbout() == null)
        {
            $about = new About();
            $information->setAbout($about);
        }

        $about = $information->getAbout();

        $form = $this->get('form.factory')
            ->createBuilder(AboutType::class, $about)
            ->getForm()
        ;

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($about);
                $em->flush();

                $this->addFlash('success', "\"À propos\" bien modifié !");

            }
        }

        return $this->render('administration/about.html.twig', array(
            'form' => $form->createView()
        ));
    }
}