<?php
// Khai báo namespace: cho PHP biết đc class này thuộc thư mục Api
namespace App\Http\Controllers\Api;

// Inpost Controller gốc để AuthController kế thừa đc các chức năng chung
use App\Http\Controllers\Controller;

// Inpost Request dể đoc đc dữ liệu client gửi lên
use Illuminate\Http\Request;

// Inpost Model User để thao tác viws bảng uses trong database
use App\Models\User;

// Inpost Hash để mã hóa và kiểm tra mật khẩu
use Illuminate\suport\Facedes\Hash;

// Inpost Rule để vết các uy tắc kiểm tra dữ liệu
use Illuminate\Validation\Rule;

// Tạo controller xử ls các chức năng xác thực tài khoản 
class AuthController extends Controller
{
    /**
     * Chức năng đăng ký tài khoản 
     * Nhâp dữ liệu -> Kiểm tra -> Tạo user -> Tọa token -> Trả kết quả
     */
    public function register(Request $request){

        // Kiểm tra dữ liệu gửi lên có hợp lệ hay ko
        // Nếu không hợp lệ, Lrv tự tả lỗi Validation (HTTP 422)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'], //Tên bắt bộc, là chuỗi, tói đa 255 từ
            'email' => ['required', 'email', 'max:255', 'unique:user,email'], // Email hợp lệ và chx có trong users
            'password' => ['requied', 'string', 'min:8', 'confirmed'] // Mật khẩu tối thiểu 8 ký tự, phải có password_confirmation khớp.
        ]);

        //Tạo bản ghi ng dùng mới trong database
        $user = User::create([
            'name' => $validated['name'], //Lấy tên đã đc kiểm tra
            'email' => $validated['email'], // Lấy email đã được kiểm tra
            'password' => Hash::make($validated['password']), // Hash mật khẩu trước khi lưu, không lưu mật khẩu gốc.
        ]);   

        // Tạo token đăng nhập để Rect dugf khi tạo API cần xác thực
        $token = $user->createToken('auth-token')->plainTextToken;

        // Trả Json cho client; HTTP 201 nghĩa là tạo tài nguyên thành công
        return response()->json([
            'message' => 'Đăng ký tà khoản thành công !',
            'user' => $user,  // Thông tin user; model User cần ẩn password và remember_token.
            'token' => $token, // Token chỉ hiển thị khi tạo; cần bảo vệ như thông tin bí mật.
        ], 201);
        
    }

    /**
     * CHỨC NĂNG: Đăng nhập.
     * CÁCH ĐỌC: Kiểm tra email/password -> tìm user -> kiểm tra hash -> tạo token.
     */
    public function login(Request $request){
        // Kiểm tra email và mật khẩu có được gửi lên đúng định dạng.
        $validated = $request->validate([
            'email' => ['require', 'email'], //Bắt buộc có email hợp lệ
            'password' => ['require', 'string'], // Bắt buộc có mật khẩu dạng chuỗi.
        ]);

        // Tìm tài khoản theo email trong database.
        $user = User::where('email', $validated['emal'])->first();

        // Nếu không có tài khoản hoặc mật khẩu sai thì từ chối đăng nhập.
        if (! $user || ! Hash::check($validated['password'], $user->password)){
                return response()->json([
                'message' => 'Email hoặc mật khẩu không chính xác.',
                ], 401); // HTTP 401: chưa xác thực thành công.
        }

        // Mật khẩu đúng thì tạo token mới cho ng dùng
        $token = $user->createToken('auth-token')->plainTextToken;

        // Trả thông báo, thông tin tài khoản và token cho React
        return response()->json([
            'message' => 'Đăng nhập thành công.',
            'user' => $user,
            'token' => $token,
        ]);
        
    }
}
