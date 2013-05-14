<?php

namespace {{ namespace }}\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
{% if 'annotation' == format.routing -%}
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
{% endif %}
use Tg\OkoaBundle\Controller\ControllerExtras;

class {{ controller }}Controller extends Controller
{
    use ControllerExtras;

{# create actions #}
{% for action in actions %}
    {% if 'annotation' == format.routing -%}
    /**
     * @Route("{{ action.route }}")
     {% if 'default' == action.template -%}
     * @Template
     {% else -%}
     * @Template("{{ action.template }}")
     {% endif -%}
     */
    {% endif -%}
    public function {{ action.name }}(
        {%- if action.placeholders|length > 0 -%}
            ${{- action.placeholders|join(', $') -}}
        {%- endif -%})
    {
        // TODO: implement action
{% if 'annotation' == format.routing %}
        return [
            {%- for placeholder in action.placeholders -%}
            '{{ placeholder }}' => ${{ placeholder }},
            {%- endfor -%}
        ];
{% endif %}
    }

{% endfor -%}
}
