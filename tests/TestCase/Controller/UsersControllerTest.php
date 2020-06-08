<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\Helper\IntegrationHelperTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\UsersController Test Case
 *
 * @uses \App\Controller\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use IntegrationHelperTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Users',
    ];

    public function testLogin()
    {
        $loginUrl = '/auth/login';

        $this->get($loginUrl);

        $this->assertResponseCode(405, 'GET Method on login should be 405 Bad Method');

        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->get(1);
        $user->set('password', 'test');

        $this->assertNotFalse($users->save($user), 'Unable to set user password');

        $data = [
            'username' => 'test',
            'password' => 'test_wrong',
        ];

        $this->post($loginUrl, $data);
        $this->assertResponseCode(401, 'Incorrect password should generate a 401 Not Authenticated');
        $result = $this->getJsonBody();
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals('FAILURE', $result['result']);

        $data['password'] = 'test';

        $this->post($loginUrl, $data);
        $this->assertResponseOk();
        $result = $this->getJsonBody();
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals('SUCCESS', $result['result']);
    }

    public function testUser()
    {
        $url = '/auth/user';

        $this->get($url);
        $this->assertResponseCode(401, 'Should return 401 if not authenticated');

        $this->_simulateLogin();

        $this->get($url);
        $this->assertResponseOk();
        $body = $this->getJsonBody();

        $this->assertArrayHasKey('user', $body);
        $this->assertEquals('1', $body['user']['id']);
    }

    protected function _simulateLogin($userId = 1)
    {
        $users = TableRegistry::get('Users');
        $user = $users->get($userId);
        $this->session(['Auth' => $user]);
    }
}
