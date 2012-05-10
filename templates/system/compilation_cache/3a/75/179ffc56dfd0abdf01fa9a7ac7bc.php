<?php

/* adm.html */
class __TwigTemplate_3a75179ffc56dfd0abdf01fa9a7ac7bc extends Twig_Template
{
    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        $context['forms'] = $this->env->loadTemplate("forms.html", true);
        // line 2
        echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<title>";
        // line 6
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "title", array(), "any", false, 6), "html");
        echo "</title>
<base href=\"";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "URL", array(), "any", false, 7), "html");
        echo "\" />
<meta http-equiv=\"Content-Type\" content=\"text/html;charset=UTF-8\" />
<link rel=\"stylesheet\" href=\"templates/system/css/main.css\" type=\"text/css\"/>

<script LANGUAGE=\"JavaScript\" SRC=\"https://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js\"></script>
<script LANGUAGE=\"JavaScript\" SRC=\"https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js\"></script>

<script LANGUAGE=\"JavaScript\" SRC=\"templates/system/js/ata.js\"></script>
<script LANGUAGE=\"JavaScript\" SRC=\"templates/system/js/function.js\"></script>
<script LANGUAGE=\"JavaScript\" SRC=\"templates/system/js/jquery-ui-1.8.10.min.js\"></script>
<link href=\"templates/system/js/jquery-ui-css-framework-1.8.10.css\" rel=\"stylesheet\" type=\"text/css\" />


";
        // line 21
        echo "    ";
        echo $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "css", array(), "any", false, 21);
        echo "
";
        // line 23
        echo "
";
        // line 25
        echo "    ";
        if ($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "js", array(), "any", false, 25)) {
            // line 26
            echo "        ";
            echo $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "js", array(), "any", false, 26);
            echo "
    ";
        }
        // line 29
        echo "
</head>
<body>
    
<div class=\"content\">

<table cellpadding=\"0\" cellspacing=\"0\">
<tr>
    <td class=\"logo\"><a href=\"adm/\"><img src=\"templates/system/img/logo.png\"></a></td>
    <td class=\"headerNavigation\">
        <a href=\"/\">Перейти на сайт</a>
        <a href=\"/?exit\">Выйти</a>
    </td>
</tr>
</table>

<div class=\"topMenu\">
    <table cellpadding=\"0\" cellspacing=\"0\">
    <tr>
        <!-- Меню -->
        ";
        // line 49
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_array_keys_filter((isset($context['_MODULES']) ? $context['_MODULES'] : null)));
        foreach ($context['_seq'] as $context['_key'] => $context['id']) {
            // line 50
            echo "            ";
            if ((($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context['_MODULES']) ? $context['_MODULES'] : null), (isset($context['id']) ? $context['id'] : null), array(), "array", false, 50), "access", array(), "any", false, 50), "use", array(), "any", false, 50), "der", array(), "any", false, 50) || $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "user", array(), "any", false, 50), "access", array(), "any", false, 50), (isset($context['id']) ? $context['id'] : null), array(), "array", false, 50), "use", array(), "any", false, 50)) || ($this->getAttribute($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "user", array(), "any", false, 50), "u_id", array(), "any", false, 50) == 1))) {
                // line 51
                echo "                ";
                if ($this->getAttribute($this->getAttribute((isset($context['_MODULES']) ? $context['_MODULES'] : null), (isset($context['id']) ? $context['id'] : null), array(), "array", false, 51), "name", array(), "any", false, 51)) {
                    // line 52
                    echo "                    <td class=\"topMenuA\"";
                    if (($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "getAction", array(), "any", false, 52) == (isset($context['id']) ? $context['id'] : null))) {
                        echo " id=\"topMenuSelected\"";
                    }
                    echo "><a href=\"adm/";
                    echo twig_escape_filter($this->env, (isset($context['id']) ? $context['id'] : null), "html");
                    echo "/\">";
                    echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context['_MODULES']) ? $context['_MODULES'] : null), (isset($context['id']) ? $context['id'] : null), array(), "array", false, 52), "name", array(), "any", false, 52), "html");
                    echo "</a></td>
                ";
                }
                // line 54
                echo "            ";
            }
            // line 55
            echo "        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['id'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 56
        echo "        <!-- /Меню -->
    </tr>
    </table>
</div>

";
        // line 62
        echo "
<!-- Содержание -->
<table cellpadding=\"0\" cellspacing=\"0\" id=\"wrapper\">
<tr valign=\"top\">
    <td style=\"width:15%\">
        <!-- Меню товаров -->
        ";
        // line 68
        if (($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "HTMLLeftMenu", array(), "any", false, 68) || $this->getAttribute($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "menu", array(), "any", false, 68), "leftMenu", array(), "any", false, 68))) {
            // line 69
            echo "            <div id=\"leftMenu\">
                ";
            // line 70
            echo $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "HTMLLeftMenu", array(), "any", false, 70);
            echo "

                ";
            // line 72
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "menu", array(), "any", false, 72), "leftMenu", array(), "any", false, 72));
            foreach ($context['_seq'] as $context['_key'] => $context['id']) {
                // line 73
                echo "                    <div class=\"level";
                echo $this->getAttribute((isset($context['id']) ? $context['id'] : null), "level", array(), "any", false, 73);
                echo "\">";
                if ($this->getAttribute((isset($context['id']) ? $context['id'] : null), "url", array(), "any", false, 73)) {
                    echo "<a href=\"";
                    echo $this->getAttribute((isset($context['id']) ? $context['id'] : null), "url", array(), "any", false, 73);
                    echo "\">";
                }
                echo $this->getAttribute((isset($context['id']) ? $context['id'] : null), "name", array(), "any", false, 73);
                if ($this->getAttribute((isset($context['id']) ? $context['id'] : null), "url", array(), "any", false, 73)) {
                    echo "</a>";
                }
                echo "</div>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['id'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 75
            echo "            </div>
        ";
        }
        // line 77
        echo "        <!-- /Меню товаров -->

    </td>
    <td id=\"content\">
            ";
        // line 81
        if ($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "navigation", array(), "any", false, 81)) {
            // line 82
            echo "                <div class=\"navigation\">";
            echo $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "navigation", array(), "any", false, 82);
            echo "</div>
            ";
        }
        // line 84
        echo "
            ";
        // line 85
        if ($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "H1", array(), "any", false, 85)) {
            // line 86
            echo "                <H1>";
            echo $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "H1", array(), "any", false, 86);
            echo "</H1>
            ";
        }
        // line 88
        echo "
            ";
        // line 89
        echo $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "error", array(), "any", false, 89);
        echo "
            ";
        // line 90
        echo $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "message", array(), "any", false, 90);
        echo "
            
            ";
        // line 92
        if ($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "pages", array(), "any", false, 92)) {
            // line 93
            echo "                <div class=\"pages\">";
            echo $this->getAttribute((isset($context['forms']) ? $context['forms'] : null), "pages", array((isset($context['Core']) ? $context['Core'] : null), ), "method", false, 93);
            echo "</div>
            ";
        }
        // line 95
        echo "
            ";
        // line 96
        if ($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "contentTpl", array(), "any", false, 96)) {
            // line 97
            echo "                ";
            $template = $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "contentTpl", array(), "any", false, 97);
            if (!$template instanceof Twig_Template) {
                $template = $this->env->loadTemplate($template);
            }
            $template->display($context);
            // line 98
            echo "            ";
        }
        // line 99
        echo "
            ";
        // line 100
        echo $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "content", array(), "any", false, 100);
        echo "

            ";
        // line 102
        if ($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "pages", array(), "any", false, 102)) {
            // line 103
            echo "                <div class=\"pages\">";
            echo $this->getAttribute((isset($context['forms']) ? $context['forms'] : null), "pages", array((isset($context['Core']) ? $context['Core'] : null), ), "method", false, 103);
            echo "</div>
            ";
        }
        // line 105
        echo "
    </td>
</tr>
</table>
<!-- /Содержание -->
</div>
";
        // line 112
        echo "
<div class=\"bottom\">
    Copyright © 2010 - ";
        // line 114
        echo twig_escape_filter($this->env, twig_date_format_filter($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "date", array(), "any", false, 114), "Y"), "html");
        echo " Powered by JD CMS &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a href=\"http://jonydi.ru/\" target=\"_blanck\" style=\"padding:10px;background:#ffff00;color:#000\">JonyDi</a>
</div>";
    }

    public function getTemplateName()
    {
        return "adm.html";
    }
}
