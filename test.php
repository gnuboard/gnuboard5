<?
echo a(2);

function a($b)
{
    global $x;

    echo $x+$b;
}

$x = 1;
?>