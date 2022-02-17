<?php

namespace app\domain\service;

use App\api\model\UserInputModel;

use App\core\shared\Utilities;

use App\domain\exception\BusinessException;
use app\domain\exception\ConnectionException;
use App\domain\exception\EntityNotFoundException;

use App\domain\model\User;

use App\domain\repository\UserRepository;

class UserService
{

    private $repository;

    public function __construct()
    {
        try {
            $this->repository = new UserRepository();
        }
        catch (ConnectionException $connectionException) {
            throw new ConnectionException($connectionException->getMessage());
        }
    }

    public function create(?UserInputModel $inputModel): ?User
    {

        $user = new User();
        $user->setEmail($inputModel->getEmail());
        $user->setPassword($inputModel->getPassword());

        $found = Utilities::toUser(
            $this->repository->findByEmail($user->getEmail())
        );

        if (($found) && ($found->__equals($user))) {
            throw new BusinessException('Already exists a User with this email');
        }

        return Utilities::toUser($this->repository->create($user));
    }

    public function findOne(?int $id): User
    {

        $user = Utilities::toUser($this->repository->findOne($id));

        if (!($user)) {
            throw new EntityNotFoundException('User Not Found');
        }

        return $user;
    }

    public function findEmail(?string $email): ?User
    {

        $user = Utilities::toUser($this->repository->findByEmail($email));

        if (!($user)) {
            throw new EntityNotFoundException('User Not Found');
        }

        return $user;
    }

    public function findAll(): ?array
    {

        $users = Utilities::toUserCollection($this->repository->findAll());

        if (!($users)) {
            throw new EntityNotFoundException('Could not find any User');
        }

        return $users;
    }

    public function update(?int $id, ?UserInputModel $inputModel): ?User
    {

        $user = new User();
        $user->setEmail($inputModel->getEmail());
        $user->setPassword($inputModel->getPassword());

        $found = $this->findOne($id);

        $found->setEmail($user->getEmail());
        $found->setPassword($user->getPassword());

        $updatedUser = Utilities::toUser($this->repository->update($found));

        if (!($updatedUser)) {
            throw new BusinessException('Could not proceed update');
        }

        return $updatedUser;
    }

    public function delete(?int $id): ?bool
    {
        $user = $this->findOne($id);

        return $this->repository->delete($user->getId());
    }

}
