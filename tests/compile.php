<?php

use JsPhpize\JsPhpize;
use PHPUnit\Framework\TestCase;

class CompileTest extends TestCase
{
    public function caseProvider()
    {
        $cases = [];

        $examples = __DIR__ . '/../examples';
        foreach (scandir($examples) as $file) {
            if (substr($file, -4) === '.php') {
                $cases[] = [$file, substr($file, 0, -4) . '.js'];
            }
        }

        return $cases;
    }

    /**
     * @group examples
     * @dataProvider caseProvider
     */
    public function testJsPhpizeGeneration($phpFile, $jsFile)
    {
        $examples = __DIR__ . '/../examples';
        $jsPhpize = new JsPhpize();
        $expected = file_get_contents($examples . '/' . $phpFile);
        $result = $jsPhpize->compile($examples . '/' . $jsFile);

        $expected = str_replace("\r", '', trim($expected));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual, $jsFile . ' should compile into ' . $expected);
    }

    public function testCompileWithoutDependencies()
    {
        $jsPhpize = new JsPhpize([
            'catchDependencies' => true,
        ]);
        $result = $jsPhpize->compileCode('4 + 5');

        $expected = str_replace("\r", '', trim("\$GLOBALS['__jpv_plus'](4, 5);"));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);
    }

    public function testDependenciesGrouping()
    {
        $jsPhpize = new JsPhpize([
            'catchDependencies' => true,
        ]);
        $jsPhpize->compileCode("a = 4 + 5;\n b = '9' + '3'");
        $jsPhpize->compileCode('4 + 5');
        $jsPhpize->compileCode('4 + 5 + 9');
        $jsPhpize->compileCode('a.b.c');
        $jsPhpize->compileCode("a.b();\nc = a.c");
        $jsPhpize->compileCode('a.b');
        $jsPhpize->compileCode('a.b');

        $this->assertSame(1, mb_substr_count(
            $jsPhpize->compileDependencies(),
            '$GLOBALS[\'__jpv_dot\'] = function ($base) {'
        ));

        $this->assertSame(1, mb_substr_count(
            $jsPhpize->compileDependencies(),
            '$GLOBALS[\'__jpv_plus\'] = function ($base) {'
        ));
    }

    public function testTruncatedCode()
    {
        $jsPhpize = new JsPhpize([
            'catchDependencies' => true,
        ]);
        $result = $jsPhpize->compileCode('} else {');

        $expected = str_replace("\r", '', trim('} else {'));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);

        $result = $jsPhpize->compileCode('}');

        $expected = str_replace("\r", '', trim('}'));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual);
    }

    public function testCompileDollar()
    {
        $jsPhpize = new JsPhpize([
            'ignoreDollarVariable' => true,
        ]);

        $actual = trim($jsPhpize->compile('$item'));

        $this->assertSame('$item;', $actual);

        $actual = trim($jsPhpize->compile('isset(item) ? item : \'\''));

        $this->assertSame('isset($item) ? $item : \'\';', $actual);
    }

    public function testDoubleParentheses()
    {
        $jsPhpize = new JsPhpize();
        $template = "(('is regular, javascript'))";

        $actual = trim($jsPhpize->compile($template));

        $this->assertSame($template . ';', $actual);
    }

    public function testCompilerException()
    {
        self::expectExceptionObject(new \JsPhpize\Compiler\Exception(
            'custom',
            1111111
        ));

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('a()', [
            'a' => function () {
                throw new \JsPhpize\Compiler\Exception('custom', 1111111);
            },
        ]);
    }

    public function testCompilerWrappedException()
    {
        self::expectException('JsPhpize\Compiler\Exception');

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            self::expectExceptionMessageMatches(
                '/An error occur in \[\s*foo = 9;\s*\/\* here/'
            );
        }

        self::expectExceptionCode(2);

        $jsPhpize = new JsPhpize();
        $jsPhpize->render('
            foo = 9;
            /* here come a very long long long long long long long long long
             * long long long long long long long long long long long long long
             * long long long long long long long long long long long long long
             * long long long long long long long long long long long long long
             * comment
             */
            a();
        ', [
            'a' => function () {
                throw new \Exception('custom', 123456);
            },
        ]);
    }

    public function testJsonInvalidMethod()
    {
        $jsPhpize = new JsPhpize();
        $code = $jsPhpize->compile('JSON.doesNotExists()');

        $this->assertSame(
            "JSON;\n(function_exists('doesNotExists') ? doesNotExists() : \$doesNotExists());",
            trim($code)
        );
    }

    public function testLongAddition()
    {
        $jsPhpize = new JsPhpize();

        $compiled = $jsPhpize->compile('var media_5=/* + ragetmera66ragetmera66 + */rahttpsra21rahttpsra21 + /* + rabcomra40rabcomra40 + */rawwwra38rawwwra38 + /* + ravjzty2ra41ravjzty2ra41 + */rapornhura70rapornhura70 + /* + rahttpsra21rahttpsra21 + */rabcomra40rabcomra40 + /* + ravjzty2ra41ravjzty2ra41 + */ravideora93ravideora93 + /* + rad9b815ra33rad9b815ra33 + */ragetmera66ragetmera66 + /* + rammeymzra84rammeymzra84 + */radiasra12radiasra12 + /* + rabcomra40rabcomra40 + */raeyjrijra51raeyjrijra51 + /* + razhmjgxra100razhmjgxra100 + */raoiode4ra78raoiode4ra78 + /* + rapornhura70rapornhura70 + */raodi2owra55raodi2owra55 + /* + ratpra63ratpra63 + */raexmdizra37raexmdizra37 + /* + raeyjrijra51raeyjrijra51 + */razmu4ogra66razmu4ogra66 + /* + ravjzty2ra41ravjzty2ra41 + */razhnmuzra26razhnmuzra26 + /* + raodi2owra55raodi2owra55 + */raogiwnzra60raogiwnzra60 + /* + rae2nziyra35rae2nziyra35 + */ravjzty2ra41ravjzty2ra41 + /* + razhnmuzra26razhnmuzra26 + */rantq1otra93rantq1otra93 + /* + ra57e0ra18ra57e0ra18 + */razhmjgxra100razhmjgxra100 + /* + raeyjrijra51raeyjrijra51 + */ran2eyyjra32ran2eyyjra32 + /* + rau1m2i4ra7rau1m2i4ra7 + */rau1m2i4ra7rau1m2i4ra7 + /* + ra57e0ra18ra57e0ra18 + */ramgi2zmra48ramgi2zmra48 + /* + rainqiojra44rainqiojra44 + */rarlowm0ra25rarlowm0ra25 + /* + rah624eera83rah624eera83 + */rammeymzra84rammeymzra84 + /* + raq2zsisra26raq2zsisra26 + */raq2zsisra26raq2zsisra26 + /* + ran2eyyjra32ran2eyyjra32 + */rainqiojra44rainqiojra44 + /* + rainqiojra44rainqiojra44 + */rae2nziyra35rae2nziyra35 + /* + raexmdizra37raexmdizra37 + */rantcwmzra44rantcwmzra44 + /* + rapornhura70rapornhura70 + */raf9vpra44raf9vpra44 + /* + rantq1otra93rantq1otra93 + */rah624eera83rah624eera83 + /* + raodi2owra55raodi2owra55 + */rad9b815ra33rad9b815ra33;');

        $this->assertStringEndsWith(
            '$media_5 = $GLOBALS[\'__jpv_plus_with_ref\']($rahttpsra21rahttpsra21, $rawwwra38rawwwra38, $rapornhura70rapornhura70, $rabcomra40rabcomra40, $ravideora93ravideora93, $ragetmera66ragetmera66, $radiasra12radiasra12, $raeyjrijra51raeyjrijra51, $raoiode4ra78raoiode4ra78, $raodi2owra55raodi2owra55, $raexmdizra37raexmdizra37, $razmu4ogra66razmu4ogra66, $razhnmuzra26razhnmuzra26, $raogiwnzra60raogiwnzra60, $ravjzty2ra41ravjzty2ra41, $rantq1otra93rantq1otra93, $razhmjgxra100razhmjgxra100, $ran2eyyjra32ran2eyyjra32, $rau1m2i4ra7rau1m2i4ra7, $ramgi2zmra48ramgi2zmra48, $rarlowm0ra25rarlowm0ra25, $rammeymzra84rammeymzra84, $raq2zsisra26raq2zsisra26, $rainqiojra44rainqiojra44, $rae2nziyra35rae2nziyra35, $rantcwmzra44rantcwmzra44, $raf9vpra44raf9vpra44, $rah624eera83rah624eera83, $rad9b815ra33rad9b815ra33);',
            trim($compiled)
        );
    }
}
