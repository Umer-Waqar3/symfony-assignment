<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

#[Route("/api",name:"api_")]

class FriendController extends AbstractFOSRestController
{
    #[Route('/friend/{username}', name: 'friend', methods:'GET')]
    public function index(string $username, ManagerRegistry $doctrine ): Response
    {
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneByUsername($username);
        $friends = [];
        foreach($user->getFriends() as $friend){
            if($friend->getStatus()){
                $friends[] = $friend->getReciver()->getUsername();
            }
            
        }
        foreach($user->getFriends2() as $friend){
            if($friend->getStatus()){
                $friends[] = $friend->getSender()->getUsername();
            }    
        }
        $data = [

            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'friends' => $friends,
        ]; 
        return $this->json( $data);
    }

    #[Route('/request/{username}', name: 'request', methods:'GET')]

    public function request(string $username, ManagerRegistry $doctrine)
    {


        $request = new Friend();
        $user = $this->getUser();
        foreach($user->getFriends() as $friend){
            if($username == $friend->getReciver()->getUsername()){
                return $this->json("request already created");
            }    
            echo $friend->getSender()->getUsername();
        }
        $repository = $doctrine->getRepository(User::class);
        $user2 = $repository->findOneByUsername($username);
        if($user2 == null){
            return $this->json("$username not found");
        }
        $request->setSender($user);
        $request->setReciver($user2);
        $request->setStatus(false);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($request);
        $entityManager->flush();
     
        return $this->json('Created new request successfully with id ' . $request->getId());  

    }

    #[Route('/requests', name: 'requests', methods:'GET')]

    public function requests()
    {
        $user = $this->getUser();
        $friends = [];
        foreach($user->getFriends2() as $friend){
            if(!$friend->getStatus()){
                $friends[] = $friend->getSender()->getUsername();
            }    
        }

        $data = [
            'requests' => $friends,
        ];

        return $this->json($data);

    }

    #[Route('/accept/{username}', name: 'accept', methods:'GET')]

    public function accept(string $username, ManagerRegistry $doctrine)
    {
        $user = $this->getUser();
        foreach($user->getFriends2() as $friend){
            if(!$friend->getStatus()){
                if($username == $friend->getSender()->getUsername()){
                    $friend->setStatus(true);
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($friend);
                    $entityManager->flush();
                    $response = $this->forward('App\Controller\FollowerController::unfollow',[
                        'username' => $username,
                    ]);
                    
                    return $this->json("request accepted");
                }
            }    
        }
        return $this->json("no request from this $username");

    }

    #[Route('/reject/{username}', name: 'reject', methods:'GET')]

    public function reject(string $username, ManagerRegistry $doctrine)
    {
        $user = $this->getUser();
        foreach($user->getFriends2() as $friend){
            if(!$friend->getStatus()){
                if($username == $friend->getSender()->getUsername()){
                    $entityManager = $doctrine->getManager();
                    $entityManager->remove($friend);
                    $entityManager->flush();
                    
                    
                    return $this->json("request rejected");
                }
            }    
        }
        return $this->json("no request from this $username");

    }

    #[Route('/unfriend/{username}', name: 'unfriend', methods:'GET')]

    public function unfriend(string $username, ManagerRegistry $doctrine ): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $this->getUser();
        foreach($user->getFriends() as $friend){
            if($friend->getStatus()){
                if($username == $friend->getReciver()->getUsername()){
                    $entityManager->remove($friend);
                    $entityManager->flush();
                    
                    
                    return $this->json("unfriended");
                }
                
            }
            
        }
        foreach($user->getFriends2() as $friend){
            if($friend->getStatus()){
                if($username == $friend->getSender()->getUsername()){
                    $entityManager->remove($friend);
                    $entityManager->flush();
                    
                    
                    return $this->json("unfriended");
                }
            }    
        }
        return $this->json("$username not found");
    }
}
