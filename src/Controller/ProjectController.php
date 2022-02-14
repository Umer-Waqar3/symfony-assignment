<?php
 
namespace App\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Project;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/api", name="api_")
 */
class ProjectController extends AbstractController
{
    /**
     * @Route("/project", name="project_index", methods={"GET"})
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $products = $doctrine
            ->getRepository(Project::class)
            ->findAll();
 
        $data = [];
 
        foreach ($products as $product) {
           $data[] = [
               'id' => $product->getId(),
               'name' => $product->getName(),
               'description' => $product->getDiscription(),
           ];
        }
 
 
        return $this->json($data);
    }
 
    /**
     * @Route("/project", name="project_new", methods={"POST"})
     */
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
 
        $project = new Project();
        $project->setName($request->request->get('name'));
        $project->setDiscription($request->request->get('discription'));
 
        $entityManager->persist($project);
        $entityManager->flush();
 
        return $this->json('Created new project successfully with id ' . $project->getId());
    }
 
    /**
     * @Route("/project/{id}", name="project_show", methods={"GET"})
     */
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $project = $doctrine
            ->getRepository(Project::class)
            ->find($id);
 
        if (!$project) {
 
            return $this->json('No project found for id' . $id, 404);
        }
 
        $data =  [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDiscription(),
        ];
         
        return $this->json($data);
    }
 
    /**
     * @Route("/project/{id}", name="project_edit", methods={"PUT"})
     */
    public function edit(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $project = $entityManager->getRepository(Project::class)->find($id);
 
        if (!$project) {
            return $this->json('No project found for id' . $id, 404);
        }
        if($request->request->get('name')!=null){
            $project->setName($request->request->get('name'));
        }
        if($request->request->get('discription')!=null){
            $project->setDiscription($request->request->get('discription'));
        }
        
        $entityManager->flush();
 
        $data =  [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDiscription(),
        ];
         
        return $this->json($data);
    }
 
    /**
     * @Route("/project/{id}", name="project_delete", methods={"DELETE"})
     */
    public function delete(int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $project = $entityManager->getRepository(Project::class)->find($id);
 
        if (!$project) {
            return $this->json('No project found for id' . $id, 404);
        }
 
        $entityManager->remove($project);
        $entityManager->flush();
 
        return $this->json('Deleted a project successfully with id ' . $id);
    }
 
 
}