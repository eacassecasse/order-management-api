<?php

namespace App\domain\service;

use App\api\model\UserInputModel;

use App\core\shared\Utilities;

use App\domain\exception\BusinessException;
use app\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;

use App\domain\exception\MYSQLTransactionException;
use App\domain\model\User;

use App\domain\repository\UserRepository;

class UserService
{

    private UserRepository $repository;

    /**
     * @throws ConnectionException
     */
    public function __construct()
    {
        try {
            $this->repository = new UserRepository();
        }
        catch (ConnectionException $connectionException) {
            throw new ConnectionException($connectionException->getMessage());
        }
    }

    /**
     * @throws MYSQLTransactionException
     * @throws BusinessException
     * @throws ConnectionException
     */
    public function create(?UserInputModel $inputModel): ?User
    {

        $user = new User();
        $user->setEmail($inputModel->getEmail());
        $user->setPassword($inputModel->getPassword());

        $found = Utilities::toUser(
            $this->repository->existsByEmail($user->getEmail())
        );

        if (($found) && ($found->__equals($user))) {
            throw new BusinessException('Already exists a User with this email');
        }

        return Utilities::toUser($this->repository->create($user));
    }

    /**
     * @throws MYSQLTransactionException
     * @throws EntityNotFoundException
     */
    public function existsByEmail(string $email): ?User
    {

        $user = Utilities::toUser($this->repository->existsByEmail($email));

        if (!($user)) {
            throw new EntityNotFoundException('User Not Found');
        }

        return $user;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws BusinessException
     * @throws EntityNotFoundException
     */
    public function update(?int $id, ?UserInputModel $inputModel): ?User
    {

        $user = new User();
        $user->setEmail($inputModel->getEmail());
        $user->setPassword($inputModel->getPassword());

        $found = $this->existsByEmail($user->getEmail());

        $found->setEmail($user->getEmail());
        $found->setPassword($user->getPassword());

        $updatedUser = Utilities::toUser($this->repository->update($found));

        if (!($updatedUser)) {
            throw new BusinessException('Could not proceed update');
        }

        return $updatedUser;
    }

    /**
     * @throws MYSQLTransactionException
     * @throws ConnectionException
     * @throws EntityNotFoundException
     */
    public function delete(?int $id): ?bool
    {
        $user = $this->findOne($id);

        return $this->repository->delete($user->getId());
    }

}
