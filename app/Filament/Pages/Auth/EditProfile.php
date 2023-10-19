<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getAvatarFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getClinicFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
