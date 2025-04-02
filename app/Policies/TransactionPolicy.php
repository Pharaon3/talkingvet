<?php

namespace App\Policies;

use App\Models\Nvoq\NvoqUser;
use App\Models\Transaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(NvoqUser $user): bool
    {

    }

    public function view(NvoqUser $user, Transaction $transaction): bool
    {
    }

    public function create(NvoqUser $user): bool
    {
    }

    public function update(NvoqUser $user, Transaction $transaction): bool
    {
    }

    public function delete(NvoqUser $user, Transaction $transaction): bool
    {
    }

    public function restore(NvoqUser $user, Transaction $transaction): bool
    {
    }

    public function forceDelete(NvoqUser $user, Transaction $transaction): bool
    {
    }
}
