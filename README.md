# TweedeGolfGeneratorBundle
Generate code for your Symfony project.


## Components and design
- `GeneratorRegistry`
    - Registry containing all currently registered generators
- `Generator`
    - Class responsible for collecting the parameters and calling the correct builders
