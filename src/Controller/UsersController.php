<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * Users Controller
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
     * Login returning a valid JWT token for upcoming requests.
     *
     * @return void
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        // regardless of POST or GET, redirect if user is logged in
        if ($result->isValid()) {
            $user = $this->Authentication->getIdentity()->getOriginalData();
            $token = JWT::encode(
                [
                    'sub' => $user->id,
                    'exp' => time() + 604800,
                ],
                Security::getSalt()
            );
            $this->response = $this->response->withAddedHeader('Authorization', "Bearer ${token}");
            $this->set([
                'result' => 'success',
                'token' => $token,
                'user' => $user,
            ]);
        } else {
            $this->response = $this->response->withStatus(403, 'Not Authenticated');
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
