<?php

use PHPUnit\Framework\TestCase;

class Menu_listTest extends TestCase
{
    public function testDeleteMenuConfirmation()
    {
        $confirmationMessage = "메뉴를 삭제하시겠습니까?\n메뉴 삭제 후 메뉴 설정의 확인 버튼을 눌러 메뉴를 저장해 주세요.";
        $expectedConfirmation = false;

        $this->expectOutputString($confirmationMessage);
        $this->assertEquals($expectedConfirmation, confirm($confirmationMessage));
    }

    public function testDeleteMenu()
    {
        $html = '
        <table id="menulist">
            <tbody>
                <tr class="menu_list">
                    <td></td>
                    <td class="btn_del_menu"></td>
                </tr>
                <tr class="menu_list">
                    <td></td>
                    <td class="btn_del_menu"></td>
                </tr>
            </tbody>
        </table>';

        $expectedHtml = '
        <table id="menulist">
            <tbody>
                <tr class="menu_list">
                    <td></td>
                </tr>
                <tr class="menu_list">
                    <td></td>
                    <td class="btn_del_menu"></td>
                </tr>
            </tbody>
        </table>';

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $menuList = $dom->getElementById('menulist');
        $buttons = $dom->getElementsByTagName('td');

        $this->assertEquals($expectedHtml, $dom->saveHTML());

        $buttons->item(1)->click();

        $this->assertEquals($expectedHtml, $dom->saveHTML());
    }

    public function testEmptyMenuList()
    {
        $html = '
        <table id="menulist">
            <tbody>
                <tr id="empty_menu_list">
                    <td class="empty_table">자료가 없습니다.</td>
                </tr>
            </tbody>
        </table>';

        $expectedHtml = '
        <table id="menulist">
            <tbody>
                <tr id="empty_menu_list">
                    <td class="empty_table">자료가 없습니다.</td>
                </tr>
            </tbody>
        </table>';

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $menuList = $dom->getElementById('menulist');
        $button = $dom->getElementById('empty_menu_list')->getElementsByTagName('td')->item(0);

        $this->assertEquals($expectedHtml, $dom->saveHTML());

        $button->click();

        $this->assertEquals($expectedHtml, $dom->saveHTML());
    }
}