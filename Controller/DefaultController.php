<?php

namespace xrow\EzPublishSolrDocsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('xrowEzPublishSolrDocsBundle:Default:index.html.twig', array('name' => $name));
    }
}
