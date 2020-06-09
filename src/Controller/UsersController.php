<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Users Controller.
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Allow unauthenticated users to reach register and login endpoints.
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['login', 'register']);
    }

    /**
     * Return the currently logged in user.
     *
     * @return void
     */
    public function user()
    {
        $this->request->allowMethod(['get']);
        $this->set([
            'user' => $this->Authentication->getIdentity()->getOriginalData(),
        ]);
    }

    /**
     * Login returning a valid JWT token for upcoming requests.
     *
     * @return void
     */
    public function login()
    {
        $this->request->allowMethod(['post']);
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result->isValid()) {
            $this->set([
                'result' => 'SUCCESS',
                'user' => $this->Authentication->getIdentity()->getOriginalData(),
            ]);
        } else {
            $this->set([
                'result' => 'FAILURE',
                'error' => $result->getStatus(),
                'details' => $result->getErrors(),
            ]);
            $this->response = $this->response->withStatus(401, 'Not Authenticated');
        }
    }

    /**
     * Logout the current session
     *
     * @return void
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result->isValid()) {
            $this->Authentication->logout();
            $this->set([
                'result' => 'logout',
            ]);
        } else {
            $this->set([
                'result' => 'no-session',
            ]);
        }
    }

    /**
     * Register a user.
     *
     * @return void
     */
    public function register()
    {
        $this->request->allowMethod(['post']);

        $user = $this->Users->newEmptyEntity();

        $user = $this->Users->patchEntity($user, $this->request->getData());
        if (!$this->Users->save($user)) {
            $this->response = $this->response->withStatus(400, 'Error saving');
        }
        $this->set(compact('user'));
    }
}
