<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Events;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/posts")
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class PostsController extends AbstractController
{
    /**
     * @Route("/{postId}/comments", defaults={"page": "1", "_format"="html"}, methods={"GET"}, name="blog_post    _index")
     * @Route("/rss.xml", defaults={"page": "1", "_format"="xml"}, methods={"GET"}, name="blog_rss")
     * @Route("/page/{page<[1-9]\d*>}", defaults={"_format"="html"}, methods={"GET"}, name="blog_index_paginated")
     * @Cache(smaxage="10")
     *
     * NOTE: For standard formats, Symfony will also automatically choose the best
     * Content-Type header for the response.
     * See https://symfony.com/doc/current/quick_tour/the_controller.html#using-formats
     */
    public function index(Request $request, int $postId, int $page, string $_format, PostRepository $posts, TagRepository $tags): Response
    {



        $data = array(
            "postId" => $postId
        );

        $response =  $this->curl_connect("https://jsonplaceholder.typicode.com/posts/".$data["postId"]."/comments", "GET", $data);

        $jsonPosts = json_decode($response);

        // Every template name also has two extensions that specify the format and
        // engine for that template.
        // See https://symfony.com/doc/current/templating.html#template-suffix
        return $this->render('posts/index.'.$_format.'.twig', ['posts' => $jsonPosts]);
    }




    function curl_connect($url, $request_type, $data = array())
    {
        if ($request_type == 'GET')
            $url .= '?' . http_build_query($data);

        $mch = curl_init();
        $headers = array(
            'Content-Type: application/json'
        );
        curl_setopt($mch, CURLOPT_URL, $url);
        curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($mch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($mch, CURLOPT_CUSTOMREQUEST, $request_type);
        curl_setopt($mch, CURLOPT_TIMEOUT, 100);
        curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false);

        if ($request_type != 'GET') {
            curl_setopt($mch, CURLOPT_POST, true);
            curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        return curl_exec($mch);
    }

}
