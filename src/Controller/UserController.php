<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route("/api",name:"api_")]

class UserController extends AbstractFOSRestController
{
    #[Route('/register', name: 'user_register', methods:'POST')]

    public function register(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $entityManager = $doctrine->getManager();

        $user = new User();
        $user->setEmail($request->request->get('email'));
        $user->setUsername($request->request->get('username'));
        $role[] = 'ROLE_USER';
        $user->setRoles($role);
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $request->request->get('password')
                )
            );

        $entityManager->persist($user);
        $entityManager->flush();
     
        return $this->json('Created new user successfully with id ' . $user->getId());    
    }

    #[Route('/user', name: 'user_get', methods:'GET')]

    public function user(): Response
    {
        $user = $this->getUser();
        foreach($user->getFollowers() as $follower){
            $followers[] = $follower->getFollowing()->getUsername();
        }
        foreach($user->getFollowing() as $following){
            $followings[] = $following->getFollowers()->getUsername();
        }
        foreach($user->getFriends() as $friend){
            $friends[] = $friend->getReciver()->getUsername();
        }
        foreach($user->getFriends2() as $friend){
            $friends[] = $friend->getSender()->getUsername();
        }
        $data = [

            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'followed_by' => $followers,
            'following' => $followings,
            'friends' => $friends,
        ];

        #dump($user->getFollowers());
        #dump($user->getFollowing());

        return $this->json( $data);
    }

    #[Route('/user', name: 'user_edit', methods:'PUT')]

    public function edit(Request $request, ManagerRegistry $doctrine):Response
    {
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();

        if($request->request->get('email')!= null)
        {
            $user->setEmail($request->request->get('email'));
        }
        if($request->request->get('username')!= null)
        {
            $user->setUsername($request->request->get('username'));
        }
        if($request->request->get('password')!= null)
        {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                        $user,
                        $request->request->get('password')
                    )
                );
        }
        $entityManager->persist($user);
        $entityManager->flush();

        $date=[
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ];

        return $this->json( $data);

    }

    #[Route('/user', name: 'user_delete', methods:'DELETE')]

    public function delete(ManagerRegistry $doctrine):Response
    {
        $user = $this->getUser();

        $entityManager = $doctrine->getManager();
        $id = $user->getId();
        $entityManager->remove($user);
        $entityManager->flush();
 
        return $this->json('Deleted a project successfully with id ' . $id);

    }

    #[Route('/user/username/{username}', name: 'user_search_username', methods:'GET')]

    public function searchu(string $username, ManagerRegistry $doctrine):Response
    {
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneByUsername($username);
        if($user==null){
            return $this->json("no user for this username");
        }
        $data[] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRoles(),
        ];
        

        return $this->json($data);


    }

    #[Route('/user/email/{email}', name: 'user_search_email', methods:'GET')]

    public function searche(string $email, ManagerRegistry $doctrine):Response
    {
        $repository = $doctrine->getRepository(User::class);
        $user = $repository->findOneByEmail($email);
        if($user==null){
            return $this->json("no user for this email");
        }
        $data[] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRoles(),
        ];
         

        return $this->json($data);


    }

    #[Route('/user/role', name: 'user_ROLE', methods:'POST')]

    public function role(Request $request, ManagerRegistry $doctrine):Response
    {
        $repository = $doctrine->getRepository(User::class);
        if($request->request->get('username')!=null)
        {
            $user = $repository->findOneByUsername($request->request->get('username'));
        }
        elseif($request->request->get('email')!=null)
        {
            $user = $repository->findOneByEmail($request->request->get('email'));
        }
        else
        {
            return $this->json("no valid input");
        }
        if($user==null){
            return $this->json("no user for this email and username");
        }
        $role[] = $request->request->get('role');
        $user->setRoles($role);
        $data[] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role' => $user->getRoles(),
        ];
         

         return $this->json($data);
        
    }

}
