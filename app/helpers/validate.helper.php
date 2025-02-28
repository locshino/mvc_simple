<?php
/**
 * Validate dữ liệu POST
 *
 * @param array $data Dữ liệu từ $_POST
 * @param array $rules Quy tắc validate (field => rule hoặc rule array)
 * ví dụ: ['email' => 'required|email', 'password' => 'required|min:6']
 * 
 * @return bool Trả về true nếu hợp lệ, false nếu không
 */
function validate(array $data, array $rules): bool
{
  $errors = []; // Mảng phẳng để lưu tất cả lỗi

  foreach ($rules as $field => $ruleSet) {
    $rules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
    $value = $data[$field] ?? '';
    $isEmpty = empty(trim($value));

    foreach ($rules as $rule) {
      if (strpos($rule, ':') !== false) {
        [$ruleName, $param] = explode(':', $rule);
      } else {
        $ruleName = $rule;
        $param = null;
      }

      switch ($ruleName) {
        case 'required':
          if ($isEmpty) {
            $errors[] = "Trường $field không được để trống!";
          }
          break;

        case 'nullable':
          if ($isEmpty) {
            continue 2;
          }
          break;

        case 'email':
          if (! $isEmpty && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Trường $field phải là email hợp lệ!";
          }
          break;

        case 'min':
          if (! $isEmpty && strlen($value) < (int) $param) {
            $errors[] = "Trường $field phải có ít nhất $param ký tự!";
          }
          break;

        case 'max':
          if (! $isEmpty && strlen($value) > (int) $param) {
            $errors[] = "Trường $field không được vượt quá $param ký tự!";
          }
          break;
      }
    }
  }

  if (! empty($errors)) {
    flashError($errors);
    return false;
  }

  return true;
}