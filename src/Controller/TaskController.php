<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Exception\ExceptionInterface;

/**
 * @Route("/task")
 */
class TaskController extends Controller
{
    /**
     * @Route("/", name="task_index")
     */
    public function indexAction()
    {
        return $this->render('task/index.html.twig', [
            'tasks' => $this->get('doctrine')->getRepository('App:Task')->findAll(),
        ]);
    }

    /**
     * @Route("/create", methods={"POST"}, name="task_create")
     */
    public function createAction(Request $request)
    {
        $task = new Task($request->request->get('title', 'title'));

        $em = $this->get('doctrine')->getManager();
        $em->persist($task);
        $em->flush();

        return $this->redirect($this->generateUrl('task_show', ['id' => $task->getId()]));
    }

    /**
     * @Route("/show/{id}", name="task_show")
     */
    public function showAction(Task $task)
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    /**
     * @Route("/apply-transition/{id}", methods={"POST"}, name="task_apply_transition")
     */
    public function applyTransitionAction(Request $request, Task $task)
    {
        try {
            $this->get('state_machine.task')
                ->apply($task, $request->request->get('transition'));

            $this->get('doctrine')->getManager()->flush();
        } catch (ExceptionInterface $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('task_show', ['id' => $task->getId()])
        );
    }

    /**
     * @Route("/reset-marking/{id}", methods={"POST"}, name="task_reset_marking")
     */
    public function resetMarkingAction(Task $task)
    {
        $task->setMarking(null);
        $this->get('doctrine')->getManager()->flush();

        return $this->redirect($this->generateUrl('task_show', ['id' => $task->getId()]));
    }
}
