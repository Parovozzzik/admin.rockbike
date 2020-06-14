<?php

namespace App\Helpers;

/**
 * Class Helper
 * @package App\Helpers
 */
class Helper
{
    /** @var string */
    const DEFAULT_VIEWS_PATH = 'App' . DS . 'Views';
    /** @var string */
    const DEFAULT_VIEWS_EXT = 'twig';

    /**
     * @param string $password
     * @return string
     */
    public static function passwordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function passwordVerify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getViewPath(string $path): string
    {
        $paths = explode('.', $path);

        $newPath = [];
        $last = array_key_last($paths);
        foreach ($paths as $key => $path) {
            if ($key !== $last) {
                $newPath[] = ucfirst($path);
            } else {
                $newPath[] = $path;
            }
        }

        $systemPath = implode(DS, $newPath);

        return self::DEFAULT_VIEWS_PATH . DS . $systemPath . '.' . self::DEFAULT_VIEWS_EXT;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateConfirmCode(int $length = 16): string
    {
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }

        return $string;
    }

    /**
     * @return string
     */
    public static function getFullDomain(): string
    {
        return getenv('DOMAIN') . ((int)getenv('PORT') !== 80 ? ':' . getenv('PORT') : '');
    }

    /**
     * @param string $text
     * @return string
     */
    public static function translit(string $text): string
    {
        $text = trim(strtolower($text));
        $converter = [
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',

            'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
            'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
            'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
            'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
            'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
            'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
            'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
        ];

        $value = strtr($text, $converter);

        $value = strtolower($value);
        $value = preg_replace('~[^-a-z0-9_]+~u', '-', $value);
        $value = trim($value, "-");


        return $value;
    }

}