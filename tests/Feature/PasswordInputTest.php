<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_preserves_ascii_passwords_and_vietnamese_names(): void
    {
        // Synthetic credentials, only stored in the in-memory test database.
        $password = ' MatKhau123! @# ';
        $this->post('/dang-ky', [
            'name' => 'Nguyễn Văn Thử',
            'email' => 'password-test@example.test',
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertRedirect('/')->assertSessionHasNoErrors();

        $user = User::where('email', 'password-test@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Nguyễn Văn Thử', $user->name);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertFalse(Hash::check(trim($password), $user->password));
    }

    public function test_registration_rejects_accented_and_non_ascii_passwords(): void
    {
        foreach (['MậtKhẩu123!', 'MatKhauđ123!', 'MatKhauĐ123!', "Ma\u{0302}tKhau123!", 'MatKhau😀123!', "MatKhau123!\n"] as $password) {
            $this->postJson('/dang-ky', [
                'name' => 'Nguyễn Văn Thử',
                'email' => 'password-test@example.test',
                'password' => $password,
                'password_confirmation' => $password,
            ])->assertUnprocessable()->assertJsonValidationErrors('password')
                ->assertJsonPath('errors.password.0', 'Mật khẩu chỉ được dùng chữ không dấu, số và ký tự đặc biệt.');
            $this->assertDatabaseCount('users', 0);
            $this->assertGuest();
        }
    }

    public function test_login_accepts_the_exact_ascii_password(): void
    {
        $user = User::create([
            'name' => 'Nguyễn Văn Thử',
            'email' => 'password-test@example.test',
            'password' => 'MatKhau123! @#',
        ]);

        $this->post('/dang-nhap', [
            'email' => $user->email,
            'password' => 'MatKhau123! @#',
        ])->assertRedirect('/')->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_blocks_accented_passwords_without_flashing_them(): void
    {
        $user = User::create([
            'name' => 'Nguyễn Văn Thử',
            'email' => 'password-test@example.test',
            'password' => 'MậtKhẩu123!',
        ]);

        $this->from('/dang-nhap')->post('/dang-nhap', [
            'email' => $user->email,
            'password' => 'MậtKhẩu123!',
        ])->assertRedirect('/dang-nhap')->assertSessionHasErrors('password')
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_wrong_ascii_password_cannot_log_in(): void
    {
        $user = User::create([
            'name' => 'Nguyễn Văn Thử',
            'email' => 'password-test@example.test',
            'password' => 'MatKhau123!',
        ]);

        $this->from('/dang-nhap')->post('/dang-nhap', [
            'email' => $user->email,
            'password' => 'matkhau123!',
        ])->assertRedirect('/dang-nhap')->assertSessionHasErrors('email')
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }
}
