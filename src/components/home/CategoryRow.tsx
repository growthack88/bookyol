import { useRef } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { categories } from '@/data/mockData';
import { useInView } from '@/hooks/useInView';

const CategoryRow = () => {
  const scrollRef = useRef<HTMLDivElement>(null);
  const [sectionRef, isInView] = useInView<HTMLElement>({ threshold: 0.2 });

  const scroll = (direction: 'left' | 'right') => {
    if (scrollRef.current) {
      const amount = direction === 'left' ? -200 : 200;
      scrollRef.current.scrollBy({ left: amount, behavior: 'smooth' });
    }
  };

  return (
    <section
      ref={sectionRef}
      className={`py-8 transition-all duration-700 ${
        isInView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
      }`}
    >
      <div className="container mx-auto px-4">
        <div className="relative">
          {/* Scroll buttons */}
          <button
            onClick={() => scroll('right')}
            className="absolute -right-2 top-1/2 z-10 hidden -translate-y-1/2 rounded-full border border-border bg-card p-2 shadow-md transition-all hover:bg-accent md:block"
            aria-label="التمرير لليمين"
          >
            <ChevronRight className="h-5 w-5" />
          </button>
          <button
            onClick={() => scroll('left')}
            className="absolute -left-2 top-1/2 z-10 hidden -translate-y-1/2 rounded-full border border-border bg-card p-2 shadow-md transition-all hover:bg-accent md:block"
            aria-label="التمرير لليسار"
          >
            <ChevronLeft className="h-5 w-5" />
          </button>

          {/* Scrollable container */}
          <div
            ref={scrollRef}
            className="scrollbar-hide flex gap-3 overflow-x-auto pb-2"
            role="list"
            aria-label="تصنيفات الكتب"
          >
            {categories.map((category, index) => (
              <button
                key={category.name}
                className="flex shrink-0 items-center gap-2 rounded-full border border-border bg-card px-4 py-2.5 transition-all hover:border-primary/30 hover:bg-accent hover:scale-[1.03] hover:shadow-md"
                style={{ animationDelay: `${index * 50}ms` }}
                role="listitem"
              >
                <span className="text-lg">{category.icon}</span>
                <span className="font-medium text-foreground">{category.name}</span>
                <span className="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                  {category.count}
                </span>
              </button>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
};

export default CategoryRow;
