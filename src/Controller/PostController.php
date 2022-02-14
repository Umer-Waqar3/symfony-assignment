<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

#[Route("/api",name:"api_")]

class PostController extends AbstractFOSRestController
{
    #[Route('/post', name: 'post', methods:'POST')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $post = new Post();
        $post->setTitle($request->request->get('title'));
        $post->setUser($user);
        if($request->request->get('content')!=null){
            $post->setContent($request->request->get('content'));
        }
        if($request->files->get('image')!= null){
            $file = $request->files->get('image');
        $uploads_directory = $this->getParameter('uploads_directory');
        $filename = md5(uniqid()). '.' . $file->guessExtension();
        $file->move(
            $uploads_directory,
            $filename
        );
        $post->setImage($filename);
        }
        
        
        
        $entityManager = $doctrine->getManager();
        $entityManager->persist($post);
        $entityManager->flush();
     
        return $this->json('Created new post successfully with id ' . $post->getId()); 
        
    }

    #[Route('/post/update/{id}', name: 'update', methods:'POST')]

    public function update(int $id,Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $repository = $doctrine->getRepository(Post::class);
        $post = $repository->find($id);
        if($post==null){
            return $this->json("post donnot exists");
        }
        if($user->getId()!=$post->getUser()->getId()){
            return $this->json("access denied");
        }
        if($request->request->get('title')!=null){
            $post->setTitle($request->request->get('title'));
        }
        if($request->request->get('content')!=null){
            $post->setContent($request->request->get('content'));
        }
        if($request->files->get('image')!= null){
            $fileSystem = new Filesystem();
            $projectDir = $this->getParameter('kernel.project_dir');
            $filename = $post->getImage();
            $fileSystem->remove($projectDir.'/public/uploads/'.$filename);
            $file = $request->files->get('image');
            $uploads_directory = $this->getParameter('uploads_directory');
            $filename = md5(uniqid()). '.' . $file->guessExtension();
            $file->move(
                $uploads_directory,
                $filename
            );
            $post->setImage($filename);
        }
        
        
        
        $entityManager = $doctrine->getManager();
        $entityManager->persist($post);
        $entityManager->flush();
     
        return $this->json('updatd post successfully with id ' . $post->getId()); 
        
    }

    #[Route('/post/delete/{id}', name: 'delete', methods:'DELETE')]

    public function delete(int $id,Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $repository = $doctrine->getRepository(Post::class);
        $post = $repository->find($id);
        if($post==null){
            return $this->json("file donnot exists");
        }
        if($user->getId()!=$post->getUser()->getId()){
            return $this->json("access denied");
        }
        
        
        
        $fileSystem = new Filesystem();
        $projectDir = $this->getParameter('kernel.project_dir');
        $filename = $post->getImage();
        $fileSystem->remove($projectDir.'/public/uploads/'.$filename);
        $id = $post->getId();
        $entityManager = $doctrine->getManager();
        $entityManager->remove($post);
        $entityManager->flush();
     
        return $this->json('updatd post successfully with id ' . $id); 
        
    }
}   
    
