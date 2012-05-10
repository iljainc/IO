<?php

/* forms.html */
class __TwigTemplate_98c1e16694056a013b7a9468e7954821 extends Twig_Template
{
    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

    }

    // line 1
    public function getpages($Core = null)
    {
        $context = array_merge($this->env->getGlobals(), array(
            "Core" => $Core,
        ));

        ob_start();
        // line 2
        echo "    ";
        if (($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "pages", array(), "any", false, 2) > 1)) {
            // line 3
            echo "        <pages>
            Страницы:
            ";
            // line 5
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable(range(1, $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "pages", array(), "any", false, 5)));
            foreach ($context['_seq'] as $context['_key'] => $context['i']) {
                // line 6
                echo "                <page";
                if (($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "page", array(), "any", false, 6) == (isset($context['i']) ? $context['i'] : null))) {
                    echo " class=\"selected\"";
                }
                echo "><a href=\"";
                echo twig_escape_filter($this->env, $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "getAction", array(), "any", false, 6), "html");
                echo "/";
                if ($this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "getObj", array(), "any", false, 6)) {
                    echo twig_escape_filter($this->env, $this->getAttribute((isset($context['Core']) ? $context['Core'] : null), "getObj", array(), "any", false, 6), "html");
                    echo "/";
                }
                echo "?page=";
                echo twig_escape_filter($this->env, (isset($context['i']) ? $context['i'] : null), "html");
                echo "\">";
                echo twig_escape_filter($this->env, (isset($context['i']) ? $context['i'] : null), "html");
                echo "</a></page>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['i'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 8
            echo "        </pages>
    ";
        }

        return ob_get_clean();
    }

    public function getTemplateName()
    {
        return "forms.html";
    }
}
