<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AppBundle\Form\CodeBarreType;
use AppBundle\Entity\Product;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(CodeBarreType::class);

        $em = $this->getDoctrine()->getManager();
        $lastV = $em->getRepository("AppBundle:Product")
                    ->findBy(array(),array('lastViewDate' => 'ASC'), 8);

        //Get bar code 
        for($i=0 ; $i < sizeof($lastV) ; $i++){
            $cb = $lastV[$i]->getBarCode();
            echo $cb." ";
        }

        /*$url = 'https://fr.openfoodfacts.org/api/v0/produit/'.$code_barre.'.json';
        $data = json_decode(file_get_contents($url), true);*/

        return [
            'form' => $form->createView(),
            'lastView' => $lastV
        ];
    }

    /**
     * @Route("/search", name="search")
     * @Template("search.html.twig")
     */
    public function searchAction(Request $request)
    {
        $form = $this->createForm(CodeBarreType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $code_barre = $data['code_barre'];

            $url = 'https://fr.openfoodfacts.org/api/v0/produit/'.$code_barre.'.json';
            $data = json_decode(file_get_contents($url), true);
            $dataPdt = $data['product'];

            //chercher si le produit existe
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository("AppBundle:Product");
            $pdt = $repo->findOneBy(['barCode' => $code_barre]);
            
            if($pdt == null){
                //le créer en base si existe pas
                $newPdt = new Product;
                $newPdt -> setBarCode($code_barre)
                        -> setNbConsultation(1)
                        -> setLastViewDate(Date('d m Y'));
                $em->persist($newPdt); 
                $em->flush();

                return [ 'code_barre' => $code_barre ];

            }else {
                //si existe incrémentez nb_consultation, maj date_lastView date now
                $nbView = $pdt -> getNbConsultation()+1;

                $pdt -> setNbConsultation($nbView);
                $pdt -> setLastViewDate(Date('d m Y'));

                $em->flush();

                return $this->render('product.html.twig', array(
                    'pdt' => $pdt, 
                    'ingredient' => $dataPdt['ingredients_text'],
                    'code_barre' => $code_barre,
                    'name' => $dataPdt['product_name'],
                    'img' => $dataPdt['image_small_url'],
                    'quantity' => $dataPdt['quantity'] ));
            }


        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/product/", name="product")
     * @Template("product.html.twig")
     */
    public function productAction()
    {
        return [];
    }
}
