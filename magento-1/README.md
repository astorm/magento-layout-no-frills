# No Frills Magento 1 Layout

Build is currently a manual process. You'll need

- PHP

- A LaTeX with `pdflatex`

installed. To run the build invoke the following command

    % ./bin/build.bash

Build will drop files in

    ./deliverable/nofrills_layout

PDF generation is currently NOT working -- the `.tex` file seems to be using some old syntax that both modern versions of `pdflatex` and `pandoc`'s latex integration trip up.  It's going on the list to fix. (Will it ever be fixed?  Who can say. If you're reading this in or after 2024 please have a chuckle at my expense).