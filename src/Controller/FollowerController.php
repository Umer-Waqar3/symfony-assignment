<?php

namespace App\Controller;

use App\Entity\Follower;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

#[Route("/api",name:"api_")]

class FollowerController extends AbstractFOSRestController
{
    #[Route('/followers/{username}', name: 'followers', methods:'GET')]

    public function index(string $username, ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneByUsername($username);
        foreach($user->getFollowers() as $follower){
            $followers[] = $follower->getFollowing()->getUsername();
        }
        
            

        $data = [

            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'followed_by' => $followers,
        ];

        return $this->json( $data);
    }

    #[Route('/following/{username}', name: 'following', methods:'GET')]
    
    public function following(string $username, ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneByUsername($username);

        
        foreach($user->getFollowing() as $following){
            $followings[] = $following->getFollowers()->getUsername();
        }
        
            

        $data = [

            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'following' => $followings,
        ];

        return $this->json( $data);
    }

    #[Route('/follow/{username}', name: 'follow', methods:'GET')]

    public function follow(string $username, ManagerRegistry $doctrine):Response
    {
        $user = $this->getUser();
        if($username == $user->getUsername()){
            return $this->json("Cannot follow yourself");
        }
        $repository = $doctrine->getRepository(User::class);
        $entityManager = $doctrine->getManager();
        foreach($user->getFollowing() as $following){
            if($username == $following->getFollowers()->getUsername()){
                return $this->json("Already following this user");
            }
        }
        $follower = new Follower();
        $follower->setFollowing($user);
        $users = $repository->findOneByUsername($username);
        $follower->setFollowers($users);
        $entityManager->persist($follower);
        $entityManager->flush();
     
        return $this->json('Created new follower successfully with id ' . $follower->getId());    

    }


    #[Route('/unfollow/{username}', name: 'unfollow', methods:'DELETE')]

    public function unfollow(string $username, ManagerRegistry $doctrine):Response
    {
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();
        foreach($user->getFollowing() as $following){
            if($username == $following->getFollowers()->getUsername()){
                $id = $following->getId();
                $entityManager->remove($following);
                $entityManager->flush();
 
                return $this->json('Deleted a follower successfully with id ' . $id);
            }
        }
        return $this->json('Not following this user');    

    }

    #[Route('/unfollow2/{id}', name: 'unfollow2', methods:'DELETE')]

    public function unfollow2(int $id, ManagerRegistry $doctrine):Response
    {
        $repository = $doctrine->getRepository(Follower::class);
        $following = $repository->find($id);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($following);
        $entityManager->flush();

        return $this->json('Deleted a follower successfully with id ' . $id);
    }
}
