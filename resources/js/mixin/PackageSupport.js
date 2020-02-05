let packages = {
    nova_inline: {
        name: 'nova-inline-relationship',
        support: {
            'name': 'NovaInline',
            'manipulates_attribute': true,
        }
    },
    nova_flexible: {
        name: 'nova-flexible-content',
        support: {
            'name': 'NovaFlexibleContent',
            'manipulates_attribute': true,
        }
    }
};

export const PackageSupport = {

    data() {
        return {
            compatabilityModes: [],
            packageSupport: {}
        }
    },

    methods: {
        registerPackageSupport(computed, package_name) {
            if(this[computed]) {
                this.addPackageSupport(package_name, packages[package_name]);
            }
        },

        addPackageSupport(package_name, support) {
            this.compatabilityModes.push(package_name);
            this.packageSupport[package_name] = support;
        },

        getPackageSpecificAttribute(name) {

        },

        addCompatabilityMode(name) {
            this.compatabilityModes.push(name);
        },

        findParentPackage(component, package_name) {
            let $parent = component.$parent;
            if(typeof $parent.field !== 'undefined' && $parent.field.component) {
                if($parent.field.component === 'nova-dependency-container') {
                    return this.findParentPackage($parent, package_name);
                }
            }

            return component.$parent;
        }
    },

};



 /*
 | Flexible Content
 |
 | @package whitecube/nova-flexible-content
 | @github https://github.com/whitecube/nova-flexible-content
 |
 */
export const NovaInlineRelationshipSupport = {

    methods: {
        getNovaInlineAttribute(dependency) {
            let nova_inline = this.$parent;
            return nova_inline.field.attribute + '_' + nova_inline.id + '_' + dependency.field;
        }
    },

    computed: {
        is_nova_inline() {
            if(this.$parent.field) {
                return this.$parent.field.component === packages.nova_inline.name;
            }
        },
    },

    mounted() {
        this.registerPackageSupport('is_nova_inline', 'nova_inline');
    }
};



/*
| Inline Relationship
|
| @package kirschbaum-development/nova-inline-relationship
| @github https://github.com/kirschbaum-development/nova-inline-relationship
|
*/
export const NovaFlexibleContentSupport = {

    methods: {
        getNovaFlexibleContentAttribute(dependency) {
            if(this.$parent.group) {
                return this.$parent.group.key + '__' + dependency.field;
            }
            return dependency.field
        }
    },

    computed: {
        is_nova_flexible_content() {
            let $parent = this.findParentPackage(this.$parent, 'nova_flexible');
            return $parent.field && $parent.field.component === packages.nova_flexible.name;
        }
    },

    mounted() {
        this.registerPackageSupport('is_nova_flexible_content', 'nova_flexible');
    }

};